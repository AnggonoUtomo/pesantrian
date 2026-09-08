<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers\StudentDisciplineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-discipline-cases')
    ->name('pesantrian.student-discipline-cases.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineController::class, 'index'])->name('index');
        Route::post('/', [StudentDisciplineController::class, 'store'])->name('store');
        Route::patch('/{case}', [StudentDisciplineController::class, 'update'])
            ->whereUlid('case')
            ->name('update');
        Route::patch('/{case}/submit', [StudentDisciplineController::class, 'submit'])
            ->whereUlid('case')
            ->name('submit');
        Route::patch('/{case}/review', [StudentDisciplineController::class, 'review'])
            ->whereUlid('case')
            ->name('review');
        Route::patch('/{case}/assign-action', [StudentDisciplineController::class, 'assignAction'])
            ->whereUlid('case')
            ->name('assign-action');
        Route::patch('/{case}/resolve', [StudentDisciplineController::class, 'resolve'])
            ->whereUlid('case')
            ->name('resolve');
        Route::patch('/{case}/void', [StudentDisciplineController::class, 'void'])
            ->whereUlid('case')
            ->name('void');
        Route::get('/{case}', [StudentDisciplineController::class, 'show'])
            ->whereUlid('case')
            ->name('show');
    });
