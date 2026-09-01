<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Santri\Presentation\Controllers\StudentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/students')
    ->name('api.v1.pesantrian.students.')
    ->group(function (): void {
        Route::get('/', [StudentApiController::class, 'index'])->name('index');
        Route::get('/{student}', [StudentApiController::class, 'show'])
            ->whereUlid('student')
            ->name('show');
    });
