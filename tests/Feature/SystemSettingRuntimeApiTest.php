<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Application\Contracts\IdempotencyRepository;
use App\Modules\System\SystemSetting\Application\DTO\IdempotencyReservationData;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\IdempotencyKeyRecord;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(SystemSettingSeeder::class);
    RateLimiter::clear('system-setting-runtime-test');
});

it('menerapkan rate limit API dari SystemSetting per actor dan endpoint', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    SystemSettingRecord::query()
        ->where('key', 'api.rate_limit.per_minute')
        ->update(['value' => json_encode(2, JSON_THROW_ON_ERROR)]);

    $this->actingAs($actor)->getJson(route('api.v1.system-settings.index'))->assertOk();
    $this->actingAs($actor)->getJson(route('api.v1.system-settings.index'))->assertOk();
    $this->actingAs($actor)->getJson(route('api.v1.system-settings.index'))->assertTooManyRequests();
});

it('memakai default rate limit 60 saat record invalid', function (): void {
    SystemSettingRecord::query()
        ->where('key', 'api.rate_limit.per_minute')
        ->update(['value' => json_encode(5000, JSON_THROW_ON_ERROR)]);

    $limiter = RateLimiter::limiter('system-api');
    $request = Request::create('/api/v1/system-settings', 'GET');
    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(60);
});

it('mereplay response idempotent untuk key dan payload yang sama', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $headers = ['Idempotency-Key' => (string) Str::ulid()];
    $payload = [
        'value' => 77,
        'reason' => 'Uji replay tanpa menyimpan credential=rahasia.',
    ];
    $url = route('api.v1.system-settings.update', 'api.rate_limit.per_minute');

    $first = $this->actingAs($actor)->patchJson($url, $payload, $headers)->assertOk();
    $second = $this->actingAs($actor)->patchJson($url, $payload, $headers)->assertOk();

    $record = IdempotencyKeyRecord::query()->firstOrFail();

    expect($second->headers->get('Idempotency-Replayed'))->toBe('true')
        ->and($second->json())->toBe($first->json())
        ->and(AuditRecord::query()->where('action', 'system_setting.updated')->count())->toBe(1)
        ->and($record->payload_hash)->toHaveLength(64)
        ->and(json_encode($record->response_body, JSON_THROW_ON_ERROR))->not->toContain('credential=rahasia');
});

it('memakai retention idempotency custom untuk waktu kedaluwarsa', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    SystemSettingRecord::query()
        ->where('key', 'api.idempotency.retention_hours')
        ->update(['value' => json_encode(2, JSON_THROW_ON_ERROR)]);
    $startedAt = now();

    $this->actingAs($actor)->patchJson(
        route('api.v1.system-settings.update', 'api.rate_limit.per_minute'),
        ['value' => 76, 'reason' => 'Uji retention dua jam.'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertOk();

    $expiresAt = IdempotencyKeyRecord::query()->firstOrFail()->expires_at;

    expect($expiresAt->betweenIncluded(
        $startedAt->copy()->addMinutes(119),
        $startedAt->copy()->addMinutes(121),
    ))->toBeTrue();
});

it('menolak key idempotency sama dengan payload berbeda', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $headers = ['Idempotency-Key' => (string) Str::ulid()];
    $url = route('api.v1.system-settings.update', 'api.rate_limit.per_minute');

    $this->actingAs($actor)->patchJson($url, [
        'value' => 70,
        'reason' => 'Payload pertama.',
    ], $headers)->assertOk();

    $this->actingAs($actor)->patchJson($url, [
        'value' => 80,
        'reason' => 'Payload berbeda.',
    ], $headers)->assertConflict();

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    expect(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(70)
        ->and(AuditRecord::query()->where('action', 'system_setting.updated')->count())->toBe(1);
});

it('mengabaikan record expired dan mewajibkan idempotency header', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $key = (string) Str::ulid();
    $url = route('api.v1.system-settings.update', 'monitoring.external_enabled');

    IdempotencyKeyRecord::query()->create([
        'actor_id' => $actor->id,
        'key' => $key,
        'endpoint' => 'PATCH api/v1/system-settings/monitoring.external_enabled',
        'payload_hash' => str_repeat('a', 64),
        'response_status' => 200,
        'response_body' => ['success' => true],
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($actor)->patchJson($url, [
        'value' => true,
        'reason' => 'Mengganti record expired.',
    ], ['Idempotency-Key' => $key])->assertOk();

    expect(IdempotencyKeyRecord::query()->count())->toBe(1)
        ->and(IdempotencyKeyRecord::query()->firstOrFail()->payload_hash)->not->toBe(str_repeat('a', 64));

    $this->actingAs($actor)->patchJson($url, [
        'value' => false,
        'reason' => 'Header sengaja kosong.',
    ])->assertUnprocessable()->assertJsonPath('message', 'Idempotency-Key wajib diisi.');
});

it('melakukan rollback mutation ketika response idempotency gagal disimpan', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $reservationId = (string) Str::ulid();
    $repository = Mockery::mock(IdempotencyRepository::class);
    $repository->shouldReceive('reserve')->once()->andReturn(new IdempotencyReservationData(
        id: $reservationId,
        payloadHash: hash('sha256', (string) json_encode([
            'reason' => 'Harus rollback.',
            'value' => 88,
        ], JSON_THROW_ON_ERROR)),
        responseStatus: 102,
        responseBody: ['pending' => true],
        expiresAt: new DateTimeImmutable('+24 hours'),
        created: true,
    ));
    $repository->shouldReceive('complete')
        ->once()
        ->andThrow(new SettingStorageUnavailable('credential-database'));
    $repository->shouldReceive('delete')->once();
    app()->instance(IdempotencyRepository::class, $repository);

    $this->actingAs($actor)->patchJson(
        route('api.v1.system-settings.update', 'api.rate_limit.per_minute'),
        ['value' => 88, 'reason' => 'Harus rollback.'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertStatus(503)
        ->assertJsonPath('message', 'Layanan konfigurasi sementara tidak tersedia.')
        ->assertDontSee('credential-database');

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    expect(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(60)
        ->and(AuditRecord::query()->where('action', 'system_setting.updated')->count())->toBe(0);
});
