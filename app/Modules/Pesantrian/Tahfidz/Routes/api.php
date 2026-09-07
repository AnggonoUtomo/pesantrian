<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Tahfidz\Presentation\Controllers\TahfidzApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/tahfidz')
    ->name('api.v1.pesantrian.tahfidz.')
    ->group(static function (): void {
        Route::get('/', [TahfidzApiController::class, 'index'])->name('index');
        Route::post('/programs', [TahfidzApiController::class, 'storeProgram'])
            ->middleware('api.idempotency')
            ->name('programs.store');
        Route::patch('/programs/{program}', [TahfidzApiController::class, 'updateProgram'])
            ->whereUlid('program')
            ->middleware('api.idempotency')
            ->name('programs.update');
        Route::post('/targets', [TahfidzApiController::class, 'storeTarget'])
            ->middleware('api.idempotency')
            ->name('targets.store');
        Route::patch('/targets/{target}', [TahfidzApiController::class, 'updateTarget'])
            ->whereUlid('target')
            ->middleware('api.idempotency')
            ->name('targets.update');
        Route::post('/submissions', [TahfidzApiController::class, 'storeSubmission'])
            ->middleware('api.idempotency')
            ->name('submissions.store');
        Route::patch('/submissions/{submission}', [TahfidzApiController::class, 'updateSubmission'])
            ->whereUlid('submission')
            ->middleware('api.idempotency')
            ->name('submissions.update');
        Route::patch('/submissions/{submission}/review', [TahfidzApiController::class, 'reviewSubmission'])
            ->whereUlid('submission')
            ->middleware('api.idempotency')
            ->name('submissions.review');
        Route::patch('/submissions/{submission}/void', [TahfidzApiController::class, 'voidSubmission'])
            ->whereUlid('submission')
            ->middleware('api.idempotency')
            ->name('submissions.void');
        Route::get('/{submission}', [TahfidzApiController::class, 'show'])
            ->whereUlid('submission')
            ->name('show');
    });
