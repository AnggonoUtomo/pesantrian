<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitoryApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/asrama')
    ->name('api.v1.pesantrian.asrama.')
    ->group(function (): void {
        Route::get('/', [DormitoryApiController::class, 'index'])->name('index');
        Route::post('/', [DormitoryApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{dormitory}', [DormitoryApiController::class, 'update'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('update');
        Route::post('/{dormitory}/rooms', [DormitoryApiController::class, 'storeRoom'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('rooms.store');
        Route::patch('/{dormitory}/rooms/{room}', [DormitoryApiController::class, 'updateRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->middleware('api.idempotency')
            ->name('rooms.update');
        Route::get('/{dormitory}', [DormitoryApiController::class, 'show'])
            ->whereUlid('dormitory')
            ->name('show');
    });
