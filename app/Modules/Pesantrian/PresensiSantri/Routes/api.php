<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers\StudentAttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-attendances')
    ->name('api.v1.pesantrian.student-attendances.')
    ->group(static function (): void {
        Route::get('/', [StudentAttendanceApiController::class, 'index'])->name('index');
        Route::post('/', [StudentAttendanceApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{attendance}', [StudentAttendanceApiController::class, 'update'])
            ->whereUlid('attendance')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{attendance}/entries', [StudentAttendanceApiController::class, 'updateEntries'])
            ->whereUlid('attendance')
            ->middleware('api.idempotency')
            ->name('entries.update');
        Route::patch('/{attendance}/submit', [StudentAttendanceApiController::class, 'submit'])
            ->whereUlid('attendance')
            ->middleware('api.idempotency')
            ->name('submit');
        Route::patch('/{attendance}/revise', [StudentAttendanceApiController::class, 'revise'])
            ->whereUlid('attendance')
            ->middleware('api.idempotency')
            ->name('revise');
        Route::patch('/{attendance}/void', [StudentAttendanceApiController::class, 'void'])
            ->whereUlid('attendance')
            ->middleware('api.idempotency')
            ->name('void');
        Route::get('/{attendance}', [StudentAttendanceApiController::class, 'show'])
            ->whereUlid('attendance')
            ->name('show');
    });
