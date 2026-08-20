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
    });
