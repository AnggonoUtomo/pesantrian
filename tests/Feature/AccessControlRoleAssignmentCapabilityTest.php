<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccessControlRoleAssignmentCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_binding_can_assign_and_revoke_a_regular_role(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
        $actor->givePermissionTo($permission);
        $capability = $this->app->make(RoleAssignmentCapability::class);

        self::assertInstanceOf(RoleAssignmentCapability::class, $capability);

        $capability->assignRole($actor, $target, $role->name);
        self::assertTrue($target->hasRole($role->name));

        $capability->revokeRole($actor, $target, $role->name);
        self::assertFalse($target->hasRole($role->name));
    }

    public function test_binding_can_replace_roles_atomically(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        $legacy = Role::create(['name' => 'Legacy', 'guard_name' => 'web']);
        $securityAdmin = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
        $support = Role::create(['name' => 'Support', 'guard_name' => 'web']);
        $actor->givePermissionTo($permission);
        $target->assignRole($legacy);
        $capability = $this->app->make(RoleAssignmentCapability::class);

        $capability->syncRoles($actor, $target, [$securityAdmin->name, $support->name]);

        self::assertSame(['SecurityAdmin', 'Support'], $target->refresh()->getRoleNames()->sort()->values()->all());
    }

    public function test_actor_without_role_assignment_permission_is_denied(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
        $capability = $this->app->make(RoleAssignmentCapability::class);

        $this->expectException(AuthorizationException::class);
        $capability->assignRole($actor, $target, 'SecurityAdmin');
    }

    public function test_regular_actor_cannot_assign_protected_super_system_role(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $actor->givePermissionTo($permission);
        $capability = $this->app->make(RoleAssignmentCapability::class);

        $this->expectException(AuthorizationException::class);
        $capability->assignRole($actor, $target, 'SuperSystem');
    }

    public function test_role_catalog_is_resolved_through_public_capability(): void
    {
        Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $catalog = $this->app->make(RoleCatalogCapability::class);

        self::assertSame(
            ['SecurityAdmin', 'SuperSystem'],
            array_map(static fn ($role): string => $role->name, $catalog->listRoles()),
        );
    }
}
