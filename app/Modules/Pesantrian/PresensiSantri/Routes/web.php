<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-attendances')
    ->name('pesantrian.student-attendances.')
    ->group(static function (): void {
        Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
        Route::post('/', [StudentAttendanceController::class, 'store'])->name('store');
        Route::patch('/{attendance}', [StudentAttendanceController::class, 'update'])
            ->whereUlid('attendance')
            ->name('update');
        Route::patch('/{attendance}/entries', [StudentAttendanceController::class, 'updateEntries'])
            ->whereUlid('attendance')
            ->name('entries.update');
        Route::patch('/{attendance}/submit', [StudentAttendanceController::class, 'submit'])
            ->whereUlid('attendance')
            ->name('submit');
        Route::patch('/{attendance}/revise', [StudentAttendanceController::class, 'revise'])
            ->whereUlid('attendance')
            ->name('revise');
        Route::patch('/{attendance}/void', [StudentAttendanceController::class, 'void'])
            ->whereUlid('attendance')
            ->name('void');
        Route::get('/{attendance}', [StudentAttendanceController::class, 'show'])
            ->whereUlid('attendance')
            ->name('show');
    });
