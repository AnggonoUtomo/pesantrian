<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitoryApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/asrama')
    ->name('api.v1.pesantrian.asrama.')
    ->group(function (): void {
        Route::get('/', [DormitoryApiController::class, 'index'])->name('index');
        Route::get('/{dormitory}', [DormitoryApiController::class, 'show'])
            ->whereUlid('dormitory')
            ->name('show');
    });
