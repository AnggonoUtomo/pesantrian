<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers\StudentPermitApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-permits')
    ->name('api.v1.pesantrian.student-permits.')
    ->group(static function (): void {
        Route::get('/', [StudentPermitApiController::class, 'index'])->name('index');
        Route::get('/{permit}', [StudentPermitApiController::class, 'show'])
            ->whereUlid('permit')
            ->name('show');
    });
