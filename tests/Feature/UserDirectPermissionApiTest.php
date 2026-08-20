<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('memberikan direct permission dengan replay dan audit tunggal', function (): void {
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();
    $headers = [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => (string) Str::ulid(),
    ];

    $first = $this->actingAs($actor)->postJson(
        route('api.v1.users.permissions.store', $target),
        ['permission' => $permission->name],
        $headers,
    )->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Direct permission user berhasil diberikan.')
        ->assertJsonPath('data.id', (string) $target->getKey())
        ->assertJsonPath('meta.correlation_id', $headers['X-Correlation-ID']);

    $second = $this->actingAs($actor)->postJson(
        route('api.v1.users.permissions.store', $target),
        ['permission' => $permission->name],
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and($target->refresh()->hasDirectPermission($permission))->toBeTrue()
        ->and(AuditRecord::query()->where('action', 'user.permission_assigned')->count())->toBe(1);
});

it('mencabut direct permission dengan replay dan audit tunggal', function (): void {
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'audit_log.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();
    $target->givePermissionTo($permission);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $first = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.permissions.destroy', [$target, $permission]),
        [],
        $headers,
    )->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Direct permission user berhasil dicabut.')
        ->assertJsonPath('data.id', (string) $target->getKey());

    $second = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.permissions.destroy', [$target, $permission]),
        [],
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and($target->refresh()->hasDirectPermission($permission))->toBeFalse()
        ->and(AuditRecord::query()->where('action', 'user.permission_revoked')->count())->toBe(1);
});

it('menolak guest dan actor tanpa permission assignment', function (): void {
    $permission = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $target = User::factory()->create();
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $this->postJson(
        route('api.v1.users.permissions.store', $target),
        ['permission' => $permission->name],
        $headers,
    )->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');

    $this->actingAs(User::factory()->create())->postJson(
        route('api.v1.users.permissions.store', $target),
        ['permission' => $permission->name],
        $headers,
    )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
});

it('menolak target protected dan permission yang tidak ditemukan', function (): void {
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $superRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protectedTarget = User::factory()->create();
    $protectedTarget->assignRole($superRole);
    $ordinaryTarget = User::factory()->create();

    $this->actingAs($actor)->postJson(
        route('api.v1.users.permissions.store', $protectedTarget),
        ['permission' => 'user.view'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($actor)->postJson(
        route('api.v1.users.permissions.store', $ordinaryTarget),
        ['permission' => 'permission.missing'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

it('menolak permission invalid dan id revoke yang bukan ULID', function (): void {
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();

    $this->actingAs($actor)->postJson(
        route('api.v1.users.permissions.store', $target),
        ['permission' => '!'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR');

    $this->actingAs($actor)->deleteJson(
        '/api/v1/users/'.$target->getKey().'/permissions/not-a-ulid',
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});
