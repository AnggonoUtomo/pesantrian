<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers\StudentDisciplineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-discipline-cases')
    ->name('pesantrian.student-discipline-cases.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineController::class, 'index'])->name('index');
        Route::get('/{case}', [StudentDisciplineController::class, 'show'])
            ->whereUlid('case')
            ->name('show');
    });
