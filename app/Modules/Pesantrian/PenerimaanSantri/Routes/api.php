<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Controllers\StudentAdmissionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/admissions')
    ->name('api.v1.pesantrian.admissions.')
    ->group(function (): void {
        Route::get('/', [StudentAdmissionApiController::class, 'index'])->name('index');
        Route::post('/', [StudentAdmissionApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{admission}', [StudentAdmissionApiController::class, 'update'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{admission}/verify', [StudentAdmissionApiController::class, 'verify'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('verify');
        Route::patch('/{admission}/accept', [StudentAdmissionApiController::class, 'accept'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('accept');
        Route::patch('/{admission}/reject', [StudentAdmissionApiController::class, 'reject'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('reject');
        Route::patch('/{admission}/cancel', [StudentAdmissionApiController::class, 'cancel'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('cancel');
    });
