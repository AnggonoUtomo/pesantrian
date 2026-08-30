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
    });
