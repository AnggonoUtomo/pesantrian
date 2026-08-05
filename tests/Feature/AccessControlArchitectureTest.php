<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Application\Actions\CreateRole;
use App\Modules\System\AccessControl\Application\Actions\DeleteRole;
use App\Modules\System\AccessControl\Application\Actions\SyncRolePermissions;
use App\Modules\System\AccessControl\Application\DTO\AccessControlDashboardData;
use App\Modules\System\AccessControl\Application\Queries\BuildAccessControlDashboard;
use App\Modules\System\AccessControl\Presentation\Controllers\RoleController;

it('keeps access control controller focused on presentation orchestration', function () {
    $controller = file_get_contents((new ReflectionClass(RoleController::class))->getFileName());

    expect($controller)
        ->not->toContain('Role::query(')
        ->not->toContain('Permission::query(')
        ->not->toContain('Role::create(')
        ->not->toContain('$role->syncPermissions(')
        ->not->toContain('$role->delete()')
        ->not->toContain('$request->validate(')
        ->toContain('BuildAccessControlDashboard')
        ->toContain('CreateRole')
        ->toContain('SyncRolePermissions')
        ->toContain('DeleteRole');
});

it('exposes the extracted application boundaries', function () {
    expect(class_exists(BuildAccessControlDashboard::class))->toBeTrue()
        ->and(class_exists(AccessControlDashboardData::class))->toBeTrue()
        ->and(class_exists(CreateRole::class))->toBeTrue()
        ->and(class_exists(SyncRolePermissions::class))->toBeTrue()
        ->and(class_exists(DeleteRole::class))->toBeTrue();
});
