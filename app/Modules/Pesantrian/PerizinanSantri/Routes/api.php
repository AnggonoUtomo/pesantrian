<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers\StudentPermitApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-permits')
    ->name('api.v1.pesantrian.student-permits.')
    ->group(static function (): void {
        Route::get('/', [StudentPermitApiController::class, 'index'])->name('index');
        Route::post('/', [StudentPermitApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{permit}', [StudentPermitApiController::class, 'update'])
            ->whereUlid('permit')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{permit}/submit', [StudentPermitApiController::class, 'submit'])
            ->whereUlid('permit')
            ->middleware('api.idempotency')
            ->name('submit');
        Route::patch('/{permit}/approve', [StudentPermitApiController::class, 'approve'])
            ->whereUlid('permit')
            ->middleware('api.idempotency')
            ->name('approve');
        Route::patch('/{permit}/reject', [StudentPermitApiController::class, 'reject'])
            ->whereUlid('permit')
            ->middleware('api.idempotency')
            ->name('reject');
        Route::get('/{permit}', [StudentPermitApiController::class, 'show'])
            ->whereUlid('permit')
            ->name('show');
    });
