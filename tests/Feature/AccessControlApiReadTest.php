<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('membaca list dan detail role melalui resource canonical', function (): void {
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $catalogPermission = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $role = Role::create(['name' => 'ApiOperator', 'guard_name' => 'web']);
    $role->givePermissionTo($catalogPermission);
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);

    $list = $this->actingAs($actor)->getJson(route('api.v1.roles.index').'?'.http_build_query([
        'search' => 'Api',
        'page' => 1,
        'per_page' => 10,
        'sort' => 'name',
    ]))->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar role berhasil dibaca.')
        ->assertJsonPath('data.0.id', $role->id)
        ->assertJsonPath('data.0.name', 'ApiOperator')
        ->assertJsonPath('data.0.guard_name', 'web')
        ->assertJsonPath('data.0.permissions.0', 'user.view')
        ->assertJsonPath('data.0.is_protected', false)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.per_page', 10);

    $this->actingAs($actor)->getJson(route('api.v1.roles.show', $role->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Detail role berhasil dibaca.')
        ->assertJsonPath('data.id', $role->id);

    expect($list->headers->get('X-Correlation-ID'))->toBeString();
});

it('membaca permission catalog dengan filter module dan pagination', function (): void {
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    Permission::create(['name' => 'audit_log.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($assign);

    $this->actingAs($actor)->getJson(route('api.v1.permissions.index').'?'.http_build_query([
        'search' => 'user.',
        'filter' => ['module' => 'user'],
        'page' => 1,
        'per_page' => 10,
        'sort' => '-name',
    ]))->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar permission berhasil dibaca.')
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'user.view')
        ->assertJsonPath('data.0.module', 'user')
        ->assertJsonPath('data.0.guard_name', 'web')
        ->assertJsonStructure([
            'data' => [['id', 'name', 'guard_name', 'module', 'label']],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('menolak guest actor tanpa permission not found dan query invalid', function (): void {
    $this->getJson(route('api.v1.roles.index'))
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)->getJson(route('api.v1.roles.index'))
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $this->actingAs($actor)->getJson(route('api.v1.roles.show', (string) Str::ulid()))
        ->assertNotFound()
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

    $this->actingAs($actor)->getJson(route('api.v1.roles.index').'?sort=id&per_page=101')
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['sort', 'per_page']]);
});
