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

it('membuat role dengan correlation audit dan replay tanpa duplikasi', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $key = (string) Str::ulid();
    $correlationId = (string) Str::ulid();
    $headers = ['Idempotency-Key' => $key, 'X-Correlation-ID' => $correlationId];

    $first = $this->actingAs($actor)->postJson(route('api.v1.roles.store'), [
        'name' => 'ApiManager',
    ], $headers)->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Role berhasil dibuat.')
        ->assertJsonPath('data.name', 'ApiManager')
        ->assertJsonPath('data.is_protected', false)
        ->assertJsonPath('meta.correlation_id', $correlationId);
    $second = $this->actingAs($actor)->postJson(route('api.v1.roles.store'), [
        'name' => 'ApiManager',
    ], $headers)->assertCreated()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(Role::query()->where('name', 'ApiManager')->count())->toBe(1)
        ->and(AuditRecord::query()->where('action', 'access_control.role.created')->count())->toBe(1)
        ->and(AuditRecord::query()->where('action', 'access_control.role.created')->firstOrFail()->correlation_id)
        ->toBe($correlationId);
});

it('memperbarui nama dan permission role secara atomik', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$manage, $assign]);
    $role = Role::create(['name' => 'ApiLama', 'guard_name' => 'web']);

    $this->actingAs($actor)->patchJson(route('api.v1.roles.update', $role->id), [
        'name' => 'ApiBaru',
        'permissions' => [$permission->name],
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Role berhasil diperbarui.')
        ->assertJsonPath('data.name', 'ApiBaru')
        ->assertJsonPath('data.permissions.0', 'user.view');

    expect($role->refresh()->name)->toBe('ApiBaru')
        ->and($role->hasPermissionTo('user.view'))->toBeTrue()
        ->and(AuditRecord::query()->whereIn('action', [
            'access_control.role.updated',
            'access_control.role.permissions_synced',
        ])->count())->toBe(2);
});

it('me-rollback rename bila actor tidak boleh menyinkronkan permission', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $role = Role::create(['name' => 'AtomicRoleLama', 'guard_name' => 'web']);

    $this->actingAs($actor)->patchJson(route('api.v1.roles.update', $role->id), [
        'name' => 'AtomicRoleBaru',
        'permissions' => [$permission->name],
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    expect($role->refresh()->name)->toBe('AtomicRoleLama')
        ->and(AuditRecord::query()->whereIn('action', [
            'access_control.role.updated',
            'access_control.role.permissions_synced',
        ])->count())->toBe(0);
});

it('menghapus role dengan idempotency dan menolak role protected atau missing', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $role = Role::create(['name' => 'RoleDihapusApi', 'guard_name' => 'web']);
    $protected = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $first = $this->actingAs($actor)->deleteJson(
        route('api.v1.roles.destroy', $role->id),
        [],
        $headers,
    )->assertOk()->assertJsonPath('data', null);
    $second = $this->actingAs($actor)->deleteJson(
        route('api.v1.roles.destroy', $role->id),
        [],
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(Role::query()->where('name', 'RoleDihapusApi')->exists())->toBeFalse()
        ->and(AuditRecord::query()->where('action', 'access_control.role.deleted')->count())->toBe(1);

    $this->actingAs($actor)->deleteJson(route('api.v1.roles.destroy', $protected->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])->assertConflict()->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($actor)->deleteJson(
        route('api.v1.roles.destroy', (string) Str::ulid()),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

it('menolak duplicate validation guest permission dan payload kosong', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $role = Role::create(['name' => 'RoleDuplikat', 'guard_name' => 'web']);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $this->postJson(route('api.v1.roles.store'), ['name' => 'GuestRole'], $headers)
        ->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)->postJson(
        route('api.v1.roles.store'),
        ['name' => 'TanpaIzin'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($actor)->postJson(
        route('api.v1.roles.store'),
        ['name' => 'RoleDuplikat'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($actor)->postJson(
        route('api.v1.roles.store'),
        ['name' => '!'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR');

    $this->actingAs($actor)->patchJson(
        route('api.v1.roles.update', $role->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR');
});
