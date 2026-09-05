<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers\StudentAttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-attendances')
    ->name('api.v1.pesantrian.student-attendances.')
    ->group(static function (): void {
        Route::get('/', [StudentAttendanceApiController::class, 'index'])->name('index');
        Route::get('/{attendance}', [StudentAttendanceApiController::class, 'show'])->name('show');
    });
