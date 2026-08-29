<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Presentation\Controllers\AcademicPeriodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('academic/periods')
    ->name('academic.periods.')
    ->group(function (): void {
        Route::get('/', [AcademicPeriodController::class, 'index'])->name('index');
    });
