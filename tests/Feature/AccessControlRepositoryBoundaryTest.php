<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Application\Actions\DeleteRole;
use App\Modules\System\AccessControl\Application\Actions\SyncRolePermissions;
use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\Contracts\PermissionCatalog;
use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;

it('mendaftarkan repository mutation catalog dan read contract', function (): void {
    expect(app(RoleRepository::class))->toBeInstanceOf(RoleRepository::class)
        ->and(app(PermissionCatalog::class))->toBeInstanceOf(PermissionCatalog::class)
        ->and(app(AccessControlReadRepository::class))->toBeInstanceOf(AccessControlReadRepository::class);
});

it('menolak mutasi protected role pada action boundary', function (): void {
    $actor = User::factory()->create();
    $manage = Permission::create(['name' => 'access_control.role.manage', 'guard_name' => 'web']);
    $actor->givePermissionTo($manage);
    $role = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);

    expect(fn () => app(DeleteRole::class)->execute($actor, (string) $role->getKey()))
        ->toThrow(AuthorizationException::class)
        ->and(Role::query()->whereKey($role->getKey())->exists())->toBeTrue();
});

it('menolak permission yang tidak ada pada action boundary', function (): void {
    $actor = User::factory()->create();
    $assign = Permission::create(['name' => 'access_control.permission.assign', 'guard_name' => 'web']);
    $actor->givePermissionTo($assign);
    $role = Role::create(['name' => 'Reviewer', 'guard_name' => 'web']);

    expect(fn () => app(SyncRolePermissions::class)->execute(
        $actor,
        (string) $role->getKey(),
        ['access_control.permission.missing'],
    ))->toThrow(InvalidArgumentException::class, 'Permission tidak valid.')
        ->and($role->fresh()->permissions)->toHaveCount(0);
});
