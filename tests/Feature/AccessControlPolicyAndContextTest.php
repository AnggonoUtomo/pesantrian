<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AccessControl\Presentation\Controllers\RoleController;
use App\Modules\System\AccessControl\Presentation\Policies\AccessControlPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class AccessControlPolicyAndContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_permission_holder_but_protects_super_system_role(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $superSystem = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $policy = $this->app->make(AccessControlPolicy::class);

        self::assertTrue($policy->viewAny($user));
        self::assertTrue($policy->update($user, $role));
        self::assertFalse($policy->update($user, $superSystem));
    }

    public function test_use_case_rechecks_and_denies_actor_without_permission(): void
    {
        $this->expectException(AuthorizationException::class);

        $service = $this->app->make(AuthorizeRoleMutation::class);
        $service->ensureAllowed(User::factory()->create());
    }

    public function test_inertia_context_contains_boolean_authorization_objects(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $user->assignRole('SuperSystem');
        $request = Request::create('/dashboard');
        $request->setUserResolver(fn (): User => $user);
        $share = $this->app->make(HandleInertiaRequests::class)->share($request);

        self::assertSame(['SuperSystem' => true], $share['auth']['roles']);
        self::assertSame(['access_control.role.manage' => true], $share['auth']['permissions']);
        self::assertTrue($share['auth']['superSystem']);
    }

    public function test_controller_declares_coarse_grained_middleware(): void
    {
        $middleware = RoleController::middleware();

        self::assertCount(5, $middleware);
    }

    public function test_super_system_gate_before_is_global_but_impersonate_still_requires_dedicated_rule(): void
    {
        $superSystem = User::factory()->create();
        $regular = User::factory()->create();
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $superSystem->assignRole('SuperSystem');

        self::assertTrue(Gate::forUser($superSystem)->allows('unregistered-ability'));
        self::assertFalse(Gate::forUser($regular)->allows('unregistered-ability'));
        self::assertFalse(Gate::forUser($superSystem)->allows('impersonate'));
    }
}
