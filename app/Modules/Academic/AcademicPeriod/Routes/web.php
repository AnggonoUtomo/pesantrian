<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Presentation\Controllers\AcademicPeriodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('academic/periods')
    ->name('academic.periods.')
    ->group(function (): void {
        Route::get('/', [AcademicPeriodController::class, 'index'])->name('index');
        Route::post('/years', [AcademicPeriodController::class, 'storeYear'])->name('years.store');
        Route::put('/years/{year}', [AcademicPeriodController::class, 'updateYear'])
            ->whereUlid('year')
            ->name('years.update');
        Route::post('/terms', [AcademicPeriodController::class, 'storeTerm'])->name('terms.store');
        Route::put('/terms/{term}', [AcademicPeriodController::class, 'updateTerm'])
            ->whereUlid('term')
            ->name('terms.update');
        Route::patch('/terms/{term}/activate', [AcademicPeriodController::class, 'activateTerm'])
            ->whereUlid('term')
            ->name('terms.activate');
        Route::patch('/terms/{term}/close', [AcademicPeriodController::class, 'closeTerm'])
            ->whereUlid('term')
            ->name('terms.close');
    });
