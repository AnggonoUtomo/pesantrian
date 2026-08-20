<?php

declare(strict_types=1);

use App\Modules\System\UserManagement\Presentation\Controllers\UserApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/users')
    ->name('api.v1.users.')
    ->group(function (): void {
        Route::get('/', [UserApiController::class, 'index'])->name('index');
        Route::post('/', [UserApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::get('/{user}', [UserApiController::class, 'show'])
            ->whereUlid('user')
            ->name('show');
        Route::patch('/{user}', [UserApiController::class, 'update'])
            ->whereUlid('user')
            ->middleware('api.idempotency')
            ->name('update');
        Route::delete('/{user}', [UserApiController::class, 'destroy'])
            ->whereUlid('user')
            ->middleware('api.idempotency')
            ->name('destroy');
        Route::post('/{user}/roles', [UserApiController::class, 'assignRole'])
            ->whereUlid('user')
            ->middleware('api.idempotency')
            ->name('roles.store');
        Route::delete('/{user}/roles/{role}', [UserApiController::class, 'revokeRole'])
            ->whereUlid('user')
            ->whereUlid('role')
            ->middleware('api.idempotency')
            ->name('roles.destroy');
        Route::post('/{user}/permissions', [UserApiController::class, 'assignPermission'])
            ->whereUlid('user')
            ->middleware('api.idempotency')
            ->name('permissions.store');
        Route::delete('/{user}/permissions/{permission}', [UserApiController::class, 'revokePermission'])
            ->whereUlid('user')
            ->whereUlid('permission')
            ->middleware('api.idempotency')
            ->name('permissions.destroy');
        Route::post('/{user}/impersonation', [UserApiController::class, 'startImpersonation'])
            ->whereUlid('user')
            ->middleware('api.idempotency')
            ->name('impersonation.store');
    });

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::delete('/impersonation', [UserApiController::class, 'endImpersonation'])
            ->middleware('api.idempotency')
            ->name('impersonation.destroy');
    });
