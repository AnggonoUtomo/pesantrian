<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Application\Actions\CreateRole;
use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccessControlAuthorizationCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_actor_with_permission_is_allowed_and_actor_without_it_is_denied(): void
    {
        $allowed = User::factory()->create();
        $denied = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $allowed->givePermissionTo($permission);
        $capability = $this->app->make(AuthorizationCapability::class);

        self::assertTrue($capability->can($allowed, $permission->name)->allowed);
        self::assertFalse($capability->can($denied, $permission->name)->allowed);
        self::assertFalse($capability->can(null, $permission->name)->allowed);
    }

    public function test_super_system_is_allowed_by_capability_and_role_check_remains_typed(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $user->assignRole('SuperSystem');
        $capability = $this->app->make(AuthorizationCapability::class);

        self::assertTrue($capability->isSuperSystem($user));
        self::assertTrue($capability->can($user, 'not-yet-defined')->allowed);
        self::assertTrue($capability->hasRole($user, 'SuperSystem')->allowed);
        self::assertFalse($capability->hasRole($user, 'MissingRole')->allowed);
    }

    public function test_create_role_returns_typed_role_data(): void
    {
        $actor = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $actor->givePermissionTo($permission);

        $role = $this->app->make(CreateRole::class)->execute($actor, 'Auditor');

        self::assertInstanceOf(RoleData::class, $role);
        self::assertSame('Auditor', $role->name);
    }
}
