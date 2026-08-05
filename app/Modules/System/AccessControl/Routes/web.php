<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Presentation\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->group(function (): void {
        Route::get('dashboard', [RoleController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('system/dashboard', [RoleController::class, 'dashboard'])
            ->name('system.dashboard');
        Route::get('system/access-control', [RoleController::class, 'index'])
            ->name('access-control.index');
        Route::post('system/access-control/roles', [RoleController::class, 'store'])
            ->name('access-control.roles.store');
        Route::put('system/access-control/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
            ->name('access-control.roles.permissions.update');
        Route::delete('system/access-control/roles/{role}', [RoleController::class, 'destroy'])
            ->name('access-control.roles.destroy');
    });
