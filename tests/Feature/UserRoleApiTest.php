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

it('memberikan role dengan replay idempotent dan audit tunggal', function (): void {
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();
    $role = Role::create(['name' => 'ApiOperator', 'guard_name' => 'web']);
    $headers = [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => (string) Str::ulid(),
    ];

    $first = $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $target),
        ['role' => $role->name],
        $headers,
    )->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Role user berhasil diberikan.')
        ->assertJsonPath('data.id', (string) $target->getKey())
        ->assertJsonPath('data.roles.0', $role->name)
        ->assertJsonPath('meta.correlation_id', $headers['X-Correlation-ID']);

    $second = $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $target),
        ['role' => $role->name],
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and($target->refresh()->hasRole($role))->toBeTrue()
        ->and(AuditRecord::query()->where('action', 'user.role_assigned')->count())->toBe(1);
});

it('mencabut role dengan replay idempotent dan audit tunggal', function (): void {
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();
    $role = Role::create(['name' => 'ApiReviewer', 'guard_name' => 'web']);
    $target->assignRole($role);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $first = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.roles.destroy', [$target, $role]),
        [],
        $headers,
    )->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Role user berhasil dicabut.')
        ->assertJsonPath('data.id', (string) $target->getKey())
        ->assertJsonPath('data.roles', []);

    $second = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.roles.destroy', [$target, $role]),
        [],
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and($target->refresh()->hasRole($role))->toBeFalse()
        ->and(AuditRecord::query()->where('action', 'user.role_revoked')->count())->toBe(1);
});

it('menolak guest dan actor tanpa permission assignment role', function (): void {
    $target = User::factory()->create();
    $role = Role::create(['name' => 'ApiRestrictedRole', 'guard_name' => 'web']);
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $this->postJson(route('api.v1.users.roles.store', $target), ['role' => $role->name], $headers)
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $this->actingAs(User::factory()->create())->postJson(
        route('api.v1.users.roles.store', $target),
        ['role' => $role->name],
        $headers,
    )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
});

it('menolak target protected dan role SuperSystem untuk actor biasa', function (): void {
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $superRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protectedTarget = User::factory()->create();
    $protectedTarget->assignRole($superRole);
    $ordinaryTarget = User::factory()->create();

    $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $protectedTarget),
        ['role' => 'ApiMissingRole'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertConflict()->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $ordinaryTarget),
        ['role' => $superRole->name],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
});

it('menolak role invalid missing dan id route yang bukan ULID', function (): void {
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);
    $target = User::factory()->create();

    $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $target),
        ['role' => '!'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR');

    $this->actingAs($actor)->postJson(
        route('api.v1.users.roles.store', $target),
        ['role' => 'ApiMissingRole'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

    $this->actingAs($actor)->deleteJson(
        '/api/v1/users/'.$target->getKey().'/roles/not-a-ulid',
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});
