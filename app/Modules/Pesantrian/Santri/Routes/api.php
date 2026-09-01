<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Santri\Presentation\Controllers\StudentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/students')
    ->name('api.v1.pesantrian.students.')
    ->group(function (): void {
        Route::get('/', [StudentApiController::class, 'index'])->name('index');
        Route::post('/', [StudentApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::post('/from-admission/{admission}', [StudentApiController::class, 'storeFromAdmission'])
            ->whereUlid('admission')
            ->middleware('api.idempotency')
            ->name('from-admission');
        Route::get('/{student}', [StudentApiController::class, 'show'])
            ->whereUlid('student')
            ->name('show');
        Route::patch('/{student}', [StudentApiController::class, 'update'])
            ->whereUlid('student')
            ->middleware('api.idempotency')
            ->name('update');
    });
