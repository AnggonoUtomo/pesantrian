<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers\StudentAchievementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/prestasi-santri')
    ->name('pesantrian.prestasi-santri.')
    ->group(function (): void {
        Route::post('/categories', [StudentAchievementController::class, 'storeCategory'])->name('categories.store');
        Route::patch('/categories/{category}', [StudentAchievementController::class, 'updateCategory'])
            ->whereUlid('category')
            ->name('categories.update');
        Route::patch('/categories/{category}/archive', [StudentAchievementController::class, 'archiveCategory'])
            ->whereUlid('category')
            ->name('categories.archive');
        Route::get('/', [StudentAchievementController::class, 'index'])->name('index');
        Route::post('/', [StudentAchievementController::class, 'store'])->name('store');
        Route::patch('/{achievement}', [StudentAchievementController::class, 'update'])
            ->whereUlid('achievement')
            ->name('update');
        Route::patch('/{achievement}/submit', [StudentAchievementController::class, 'submit'])
            ->whereUlid('achievement')
            ->name('submit');
        Route::patch('/{achievement}/verify', [StudentAchievementController::class, 'verify'])
            ->whereUlid('achievement')
            ->name('verify');
        Route::patch('/{achievement}/revise', [StudentAchievementController::class, 'revise'])
            ->whereUlid('achievement')
            ->name('revise');
        Route::patch('/{achievement}/void', [StudentAchievementController::class, 'void'])
            ->whereUlid('achievement')
            ->name('void');
        Route::get('/{achievement}', [StudentAchievementController::class, 'show'])
            ->whereUlid('achievement')
            ->name('show');
    });
