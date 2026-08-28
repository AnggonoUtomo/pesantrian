<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Presentation\Controllers\AcademicTermApiController;
use App\Modules\Academic\AcademicPeriod\Presentation\Controllers\AcademicYearApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/academic/periods')
    ->name('api.v1.academic.periods.')
    ->group(function (): void {
        Route::prefix('years')
            ->name('years.')
            ->group(function (): void {
                Route::get('/', [AcademicYearApiController::class, 'index'])->name('index');
                Route::post('/', [AcademicYearApiController::class, 'store'])
                    ->middleware('api.idempotency')
                    ->name('store');
                Route::patch('/{year}', [AcademicYearApiController::class, 'update'])
                    ->whereUlid('year')
                    ->middleware('api.idempotency')
                    ->name('update');
            });

        Route::prefix('terms')
            ->name('terms.')
            ->group(function (): void {
                Route::get('/', [AcademicTermApiController::class, 'index'])->name('index');
                Route::get('/current', [AcademicTermApiController::class, 'current'])->name('current');
                Route::post('/', [AcademicTermApiController::class, 'store'])
                    ->middleware('api.idempotency')
                    ->name('store');
                Route::patch('/{term}/activate', [AcademicTermApiController::class, 'activate'])
                    ->whereUlid('term')
                    ->middleware('api.idempotency')
                    ->name('activate');
                Route::patch('/{term}/close', [AcademicTermApiController::class, 'close'])
                    ->whereUlid('term')
                    ->middleware('api.idempotency')
                    ->name('close');
                Route::patch('/{term}', [AcademicTermApiController::class, 'update'])
                    ->whereUlid('term')
                    ->middleware('api.idempotency')
                    ->name('update');
            });
    });
