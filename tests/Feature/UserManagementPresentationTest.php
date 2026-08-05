<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\UserManagement\Presentation\Policies\UserManagementPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('mendaftarkan route list user pada boundary module', function (): void {
    expect(route('system.users.index'))->toContain('/system/users');
});

it('mengizinkan actor dengan user.view dan menolak actor tanpa permission', function (): void {
    $permission = Permission::create([
        'name' => 'user.view',
        'guard_name' => 'web',
    ]);
    $authorized = User::factory()->create();
    $authorized->givePermissionTo($permission);
    $unauthorized = User::factory()->create();

    $this->actingAs($authorized)->get(route('system.users.index'))->assertOk();
    $this->actingAs($unauthorized)->get(route('system.users.index'))->assertForbidden();
});

it('mengirim role option typed dan mengizinkan assignment melalui capability publik', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo([$view, $update, $assign]);
    Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);

    $this->actingAs($actor)
        ->get(route('system.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('roles', 1)
            ->where('roles.0.name', 'SecurityAdmin')
        );

    $this->actingAs($actor)
        ->patch(route('system.users.roles', $target), ['role' => 'SecurityAdmin'])
        ->assertRedirect();

    expect($target->refresh()->hasRole('SecurityAdmin'))->toBeTrue();
});

it('menolak assignment role jika actor tidak memiliki permission assignment', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo($update);
    Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);

    $this->actingAs($actor)
        ->patch(route('system.users.roles', $target), ['role' => 'SecurityAdmin'])
        ->assertForbidden();

    expect($target->refresh()->hasRole('SecurityAdmin'))->toBeFalse();
});

it('policy menolak mutation terhadap SuperSystem', function (): void {
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $superSystem = User::factory()->create();
    $role = Role::create([
        'name' => 'SuperSystem',
        'guard_name' => 'web',
    ]);
    $superSystem->assignRole($role);

    expect(Gate::forUser($actor)->allows('delete', $target))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('delete', $superSystem))->toBeFalse()
        ->and(app(UserManagementPolicy::class)->delete($actor, $superSystem))->toBeFalse();
});

it('controller hanya menjadi orchestration layer', function (): void {
    $source = file_get_contents(app_path('Modules/System/UserManagement/Presentation/Controllers/UserController.php'));

    expect($source)->not->toContain('::query(')
        ->and($source)->not->toContain('->where(')
        ->and($source)->not->toContain('->get(');
});
