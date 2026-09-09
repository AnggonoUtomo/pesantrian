<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers\StudentAchievementApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/prestasi-santri')
    ->name('api.v1.pesantrian.prestasi-santri.')
    ->group(static function (): void {
        Route::get('/categories', [StudentAchievementApiController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [StudentAchievementApiController::class, 'storeCategory'])
            ->middleware('api.idempotency')
            ->name('categories.store');
        Route::patch('/categories/{category}', [StudentAchievementApiController::class, 'updateCategory'])
            ->whereUlid('category')
            ->middleware('api.idempotency')
            ->name('categories.update');
        Route::patch('/categories/{category}/archive', [StudentAchievementApiController::class, 'archiveCategory'])
            ->whereUlid('category')
            ->middleware('api.idempotency')
            ->name('categories.archive');
        Route::get('/', [StudentAchievementApiController::class, 'index'])->name('index');
        Route::post('/', [StudentAchievementApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{achievement}', [StudentAchievementApiController::class, 'update'])
            ->whereUlid('achievement')
            ->middleware('api.idempotency')
            ->name('update');
        Route::get('/{achievement}', [StudentAchievementApiController::class, 'show'])
            ->whereUlid('achievement')
            ->name('show');
    });
