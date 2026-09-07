<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Tahfidz\Presentation\Controllers\TahfidzController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/tahfidz')
    ->name('pesantrian.tahfidz.')
    ->group(static function (): void {
        Route::get('/', [TahfidzController::class, 'index'])->name('index');
        Route::get('/{submission}', [TahfidzController::class, 'show'])
            ->whereUlid('submission')
            ->name('show');
    });
