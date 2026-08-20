<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('memulai impersonation tanpa mengekspos session dan replay sebagai actor asli', function (): void {
    $permission = Permission::create(['name' => 'user.impersonate', 'guard_name' => 'web']);
    $actor = User::factory()->create(['name' => 'Actor Aman']);
    $actor->givePermissionTo($permission);
    $target = User::factory()->create(['name' => 'Target Aman', 'status' => UserStatus::ACTIVE]);
    $headers = [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => (string) Str::ulid(),
    ];
    $payload = ['reason' => 'Investigasi dukungan yang telah disetujui.'];

    $first = $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $target),
        $payload,
        $headers,
    )->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Impersonation berhasil dimulai.')
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.actor_name', 'Actor Aman')
        ->assertJsonPath('data.target_name', 'Target Aman')
        ->assertJsonPath('meta.correlation_id', $headers['X-Correlation-ID'])
        ->assertJsonMissingPath('data.actor_id')
        ->assertJsonMissingPath('data.target_id')
        ->assertJsonMissingPath('data.session')
        ->assertJsonMissingPath('data.token')
        ->assertJsonMissingPath('data.reason');

    $this->assertAuthenticatedAs($target);

    $second = $this->postJson(
        route('api.v1.users.impersonation.store', $target),
        $payload,
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(AuditRecord::query()->where('action', 'user.impersonation_started')->count())->toBe(1);
});

it('mengakhiri impersonation memulihkan actor dan dapat direplay', function (): void {
    $permission = Permission::create(['name' => 'user.impersonate', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($permission);
    $target = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $target),
        ['reason' => 'Pemulihan sesi dukungan yang disetujui.'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertOk();
    $this->assertAuthenticatedAs($target);

    $headers = ['Idempotency-Key' => (string) Str::ulid()];
    $first = $this->deleteJson(route('api.v1.impersonation.destroy'), [], $headers)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Impersonation berhasil diakhiri.')
        ->assertJsonPath('data', null);
    $this->assertAuthenticatedAs($actor);

    $second = $this->deleteJson(route('api.v1.impersonation.destroy'), [], $headers)
        ->assertOk()
        ->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(AuditRecord::query()->where('action', 'user.impersonation_started')->count())->toBe(1)
        ->and(AuditRecord::query()->where('action', 'user.impersonation_ended')->count())->toBe(1);
});

it('menolak reason impersonation yang tidak memenuhi contract', function (): void {
    $permission = Permission::create(['name' => 'user.impersonate', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($permission);
    $target = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $target),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'IMPERSONATION_REASON_REQUIRED');

    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $target),
        ['reason' => 'pendek'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'IMPERSONATION_REASON_REQUIRED');
});

it('menolak guest actor tanpa izin dan target yang dilarang', function (): void {
    $permission = Permission::create(['name' => 'user.impersonate', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($permission);
    $target = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $reason = ['reason' => 'Pemeriksaan keamanan yang telah disetujui.'];

    $this->postJson(
        route('api.v1.users.impersonation.store', $target),
        $reason,
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');

    $this->actingAs(User::factory()->create())->postJson(
        route('api.v1.users.impersonation.store', $target),
        $reason,
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $actor),
        $reason,
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'IMPERSONATION_TARGET_FORBIDDEN');

    $superRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protected = User::factory()->create();
    $protected->assignRole($superRole);
    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $protected),
        $reason,
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'IMPERSONATION_TARGET_FORBIDDEN');

    $inactive = User::factory()->create(['status' => UserStatus::INACTIVE]);
    $this->actingAs($actor)->postJson(
        route('api.v1.users.impersonation.store', $inactive),
        $reason,
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'IMPERSONATION_TARGET_FORBIDDEN');
});

it('menolak end ketika session impersonation tidak aktif', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)->deleteJson(
        route('api.v1.impersonation.destroy'),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'CONFLICT');
});
