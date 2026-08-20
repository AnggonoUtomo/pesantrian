<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\IdempotencyKeyRecord;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('membuat user dengan envelope canonical audit correlation dan tanpa password', function (): void {
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($create);
    $correlationId = (string) Str::ulid();

    $response = $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'User API Baru',
        'email' => 'user-api-baru@example.test',
        'password' => 'dummy-password-aman',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $correlationId,
    ])->assertCreated()
        ->assertHeader('X-Correlation-ID', $correlationId)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User berhasil dibuat.')
        ->assertJsonPath('data.email', 'user-api-baru@example.test')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('meta.correlation_id', $correlationId)
        ->assertJsonMissingPath('data.password');

    $audit = AuditRecord::query()->where('action', 'user.created')->firstOrFail();

    $created = User::query()->where('email', 'user-api-baru@example.test')->firstOrFail();

    expect($created->password)->not->toBe('dummy-password-aman')
        ->and($audit->correlation_id)->toBe($correlationId)
        ->and($audit->metadata)->not->toHaveKey('password')
        ->and($response->getContent())->not->toContain('dummy-password-aman');
});

it('mereplay create tanpa membuat user atau audit duplikat', function (): void {
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($create);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];
    $payload = [
        'name' => 'User Replay',
        'email' => 'user-replay@example.test',
        'password' => 'dummy-password-replay',
    ];

    $first = $this->actingAs($actor)->postJson(route('api.v1.users.store'), $payload, $headers)
        ->assertCreated();
    $second = $this->actingAs($actor)->postJson(route('api.v1.users.store'), $payload, $headers)
        ->assertCreated()
        ->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(User::query()->where('email', 'user-replay@example.test')->count())->toBe(1)
        ->and(AuditRecord::query()->where('action', 'user.created')->count())->toBe(1)
        ->and(json_encode(
            IdempotencyKeyRecord::query()->firstOrFail()->response_body,
            JSON_THROW_ON_ERROR,
        ))->not->toContain('dummy-password-replay')
        ->and($second->getContent())->not->toContain('dummy-password-replay');
});

it('menolak duplicate validation permission dan idempotency header secara aman', function (): void {
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($create);
    User::factory()->create(['email' => 'duplicate-api@example.test']);

    $this->postJson(route('api.v1.users.store'), [
        'name' => 'Guest API',
        'email' => 'guest-api@example.test',
        'password' => 'dummy-password-guest',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'Duplicate API',
        'email' => 'duplicate-api@example.test',
        'password' => 'dummy-password-duplicate',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertConflict()
        ->assertJsonPath('code', 'CONFLICT')
        ->assertDontSee('dummy-password-duplicate');

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'X',
        'email' => 'invalid-email',
        'password' => 'short',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)->postJson(route('api.v1.users.store'), [
        'name' => 'Tanpa Izin',
        'email' => 'tanpa-izin@example.test',
        'password' => 'dummy-password-no-access',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'Status Tanpa Izin',
        'email' => 'status-tanpa-izin@example.test',
        'password' => 'dummy-password-status',
        'status' => 'suspended',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'Tanpa Key',
        'email' => 'tanpa-key@example.test',
        'password' => 'dummy-password-no-key',
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    expect(User::query()->whereIn('email', [
        'tanpa-izin@example.test',
        'tanpa-key@example.test',
        'status-tanpa-izin@example.test',
        'guest-api@example.test',
    ])->exists())->toBeFalse();
});

it('menerapkan rate limit per actor endpoint pada create user', function (): void {
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($create);
    SystemSettingRecord::query()
        ->where('key', 'api.rate_limit.per_minute')
        ->update(['value' => json_encode(1, JSON_THROW_ON_ERROR)]);

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'Rate Pertama',
        'email' => 'rate-pertama@example.test',
        'password' => 'dummy-password-rate-one',
    ], ['Idempotency-Key' => (string) Str::ulid()])->assertCreated();

    $this->actingAs($actor)->postJson(route('api.v1.users.store'), [
        'name' => 'Rate Kedua',
        'email' => 'rate-kedua@example.test',
        'password' => 'dummy-password-rate-two',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertTooManyRequests()
        ->assertJsonPath('code', 'RATE_LIMITED');

    expect(User::query()->where('email', 'rate-kedua@example.test')->exists())->toBeFalse();
});
