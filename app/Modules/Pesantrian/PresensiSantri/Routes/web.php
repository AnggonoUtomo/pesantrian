<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-attendances')
    ->name('pesantrian.student-attendances.')
    ->group(static function (): void {
        Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
        Route::get('/{attendance}', [StudentAttendanceController::class, 'show'])
            ->whereUlid('attendance')
            ->name('show');
    });
