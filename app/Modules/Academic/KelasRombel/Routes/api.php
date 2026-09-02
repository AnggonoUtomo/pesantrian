<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Presentation\Controllers\ClassGroupApiController;
use App\Modules\Academic\KelasRombel\Presentation\Controllers\ClassLevelApiController;
use App\Modules\Academic\KelasRombel\Presentation\Controllers\CurriculumApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/academic/class-groups')
    ->name('api.v1.academic.class-groups.')
    ->group(function (): void {
        Route::prefix('curricula')
            ->name('curricula.')
            ->group(function (): void {
                Route::post('/', [CurriculumApiController::class, 'store'])
                    ->middleware('api.idempotency')
                    ->name('store');
                Route::patch('/{curriculum}', [CurriculumApiController::class, 'update'])
                    ->whereUlid('curriculum')
                    ->middleware('api.idempotency')
                    ->name('update');
            });

        Route::prefix('levels')
            ->name('levels.')
            ->group(function (): void {
                Route::post('/', [ClassLevelApiController::class, 'store'])
                    ->middleware('api.idempotency')
                    ->name('store');
                Route::patch('/{level}', [ClassLevelApiController::class, 'update'])
                    ->whereUlid('level')
                    ->middleware('api.idempotency')
                    ->name('update');
            });

        Route::get('/', [ClassGroupApiController::class, 'index'])->name('index');
        Route::post('/', [ClassGroupApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{classGroup}', [ClassGroupApiController::class, 'update'])
            ->whereUlid('classGroup')
            ->middleware('api.idempotency')
            ->name('update');
        Route::get('/{classGroup}', [ClassGroupApiController::class, 'show'])
            ->whereUlid('classGroup')
            ->name('show');
    });
