<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Presentation\Controllers\AccessControlApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/roles', [AccessControlApiController::class, 'roles'])->name('roles.index');
        Route::post('/roles', [AccessControlApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('roles.store');
        Route::get('/roles/{role}', [AccessControlApiController::class, 'role'])
            ->whereUlid('role')
            ->name('roles.show');
        Route::patch('/roles/{role}', [AccessControlApiController::class, 'update'])
            ->whereUlid('role')
            ->middleware('api.idempotency')
            ->name('roles.update');
        Route::delete('/roles/{role}', [AccessControlApiController::class, 'destroy'])
            ->whereUlid('role')
            ->middleware('api.idempotency')
            ->name('roles.destroy');
        Route::get('/permissions', [AccessControlApiController::class, 'permissions'])
            ->name('permissions.index');
    });
