<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers\StudentDisciplineApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-discipline-categories')
    ->name('api.v1.pesantrian.student-discipline-categories.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineApiController::class, 'categories'])->name('index');
    });

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-discipline-cases')
    ->name('api.v1.pesantrian.student-discipline-cases.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineApiController::class, 'index'])->name('index');
        Route::get('/{case}', [StudentDisciplineApiController::class, 'show'])
            ->whereUlid('case')
            ->name('show');
    });
