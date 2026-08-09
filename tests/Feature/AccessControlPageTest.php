<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccessControlPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_login_menampilkan_konteks_area_system(): void
    {
        $this->get(route('system.login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/login')
                ->where('area', 'system')
            );
    }

    public function test_authorized_actor_receives_typed_access_control_props(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->get(route('access-control.index'));

        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component('System/AccessControl/pages/Index')
            ->has('roles', 1)
            ->has('permissionGroups', 1)
            ->where('selectedRoleId', fn ($value): bool => is_string($value))
        );
    }

    public function test_actor_with_permission_assign_can_open_access_control_for_sync_workflow(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        $this->actingAs($user)
            ->get(route('access-control.index'))
            ->assertOk();
    }

    public function test_actor_without_manage_permission_receives_server_side_forbidden(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('access-control.index'));

        $response->assertForbidden();
    }

    public function test_authorized_actor_receives_system_dashboard_props(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'system.dashboard.view', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->get(route('system.dashboard'));

        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component('System/Dashboard')
            ->has('roles', 1)
            ->has('permissionGroups', 1)
        );
    }

    public function test_actor_without_manage_permission_cannot_open_system_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('system.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_alias_menampilkan_system_dashboard(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'system.dashboard.view', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->component('System/Dashboard'));
    }

    public function test_authorized_actor_can_sync_permissions_for_editable_role(): void
    {
        $user = User::factory()->create();
        $permissionAssign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
        $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        $user->givePermissionTo($permissionAssign);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->put(route('access-control.roles.permissions.update', $role), [
            'permissions' => [$assign->name],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast', [
                'type' => 'success',
                'message' => 'Permission role berhasil diperbarui.',
            ]);
        self::assertTrue($role->fresh()->hasPermissionTo($assign));
    }

    public function test_actor_with_role_manage_but_without_permission_assign_cannot_sync_permissions(): void
    {
        $user = User::factory()->create();
        $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        $user->givePermissionTo($manage);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        $this->actingAs($user)
            ->put(route('access-control.roles.permissions.update', $role), [
                'permissions' => [$assign->name],
            ])
            ->assertForbidden();

        self::assertFalse($role->fresh()->hasPermissionTo($assign));
    }

    public function test_super_system_role_cannot_sync_permissions(): void
    {
        $user = User::factory()->create();
        $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        $user->givePermissionTo($manage);
        $role = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);

        $response = $this->actingAs($user)->put(route('access-control.roles.permissions.update', $role), [
            'permissions' => [$assign->name],
        ]);

        $response->assertForbidden();
        self::assertFalse($role->fresh()->hasPermissionTo($assign));
    }

    public function test_super_system_can_manage_regular_role_but_cannot_mutate_super_system_role(): void
    {
        $superSystemUser = User::factory()->create();
        $superSystem = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $superSystemUser->assignRole($superSystem);
        $regularRole = Role::create(['name' => 'Reviewer', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);

        $this->actingAs($superSystemUser)
            ->put(route('access-control.roles.permissions.update', $regularRole), [
                'permissions' => [$permission->name],
            ])
            ->assertRedirect();

        self::assertTrue($regularRole->fresh()->hasPermissionTo($permission));

        $this->actingAs($superSystemUser)
            ->put(route('access-control.roles.permissions.update', $superSystem), [
                'permissions' => [$permission->name],
            ])
            ->assertForbidden();

        self::assertFalse($superSystem->fresh()->hasPermissionTo($permission));
    }

    public function test_authorized_actor_can_create_role(): void
    {
        $user = User::factory()->create();
        $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($manage);

        $response = $this->actingAs($user)->post(route('access-control.roles.store'), [
            'name' => 'Reviewer',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast', [
                'type' => 'success',
                'message' => 'Role berhasil ditambahkan.',
            ]);
        self::assertDatabaseHas('roles', ['name' => 'Reviewer', 'guard_name' => 'web']);
    }

    public function test_actor_without_manage_permission_cannot_create_role(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('access-control.roles.store'), ['name' => 'Reviewer'])
            ->assertForbidden();
    }

    public function test_authorized_actor_can_delete_editable_role(): void
    {
        $user = User::factory()->create();
        $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($manage);
        $role = Role::create(['name' => 'Reviewer', 'guard_name' => 'web']);

        $response = $this->actingAs($user)
            ->delete(route('access-control.roles.destroy', $role))
            ->assertRedirect();

        $response->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Role berhasil dihapus.',
        ]);

        self::assertDatabaseMissing('roles', ['id' => $role->getKey()]);
    }

    public function test_super_system_role_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($manage);
        $role = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);

        $this->actingAs($user)
            ->delete(route('access-control.roles.destroy', $role))
            ->assertForbidden();

        self::assertDatabaseHas('roles', ['id' => $role->getKey()]);
    }
}
