<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Tahfidz\Presentation\Controllers\TahfidzApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/tahfidz')
    ->name('api.v1.pesantrian.tahfidz.')
    ->group(static function (): void {
        Route::get('/', [TahfidzApiController::class, 'index'])->name('index');
        Route::get('/{submission}', [TahfidzApiController::class, 'show'])
            ->whereUlid('submission')
            ->name('show');
    });
