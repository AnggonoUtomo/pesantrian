<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers\StudentAchievementApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/prestasi-santri')
    ->name('api.v1.pesantrian.prestasi-santri.')
    ->group(static function (): void {
        Route::get('/categories', [StudentAchievementApiController::class, 'categories'])->name('categories.index');
        Route::get('/', [StudentAchievementApiController::class, 'index'])->name('index');
        Route::get('/{achievement}', [StudentAchievementApiController::class, 'show'])
            ->whereUlid('achievement')
            ->name('show');
    });
