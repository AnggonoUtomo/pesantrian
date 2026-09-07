<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Tahfidz\Presentation\Controllers\TahfidzController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/tahfidz')
    ->name('pesantrian.tahfidz.')
    ->group(static function (): void {
        Route::get('/', [TahfidzController::class, 'index'])->name('index');
        Route::post('/programs', [TahfidzController::class, 'storeProgram'])->name('programs.store');
        Route::patch('/programs/{program}', [TahfidzController::class, 'updateProgram'])
            ->whereUlid('program')
            ->name('programs.update');
        Route::post('/targets', [TahfidzController::class, 'storeTarget'])->name('targets.store');
        Route::patch('/targets/{target}', [TahfidzController::class, 'updateTarget'])
            ->whereUlid('target')
            ->name('targets.update');
        Route::post('/submissions', [TahfidzController::class, 'storeSubmission'])->name('submissions.store');
        Route::patch('/submissions/{submission}', [TahfidzController::class, 'updateSubmission'])
            ->whereUlid('submission')
            ->name('submissions.update');
        Route::patch('/submissions/{submission}/review', [TahfidzController::class, 'reviewSubmission'])
            ->whereUlid('submission')
            ->name('submissions.review');
        Route::patch('/submissions/{submission}/void', [TahfidzController::class, 'voidSubmission'])
            ->whereUlid('submission')
            ->name('submissions.void');
        Route::get('/{submission}', [TahfidzController::class, 'show'])
            ->whereUlid('submission')
            ->name('show');
    });
