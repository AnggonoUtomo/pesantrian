<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers\StudentPermitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-permits')
    ->name('pesantrian.student-permits.')
    ->group(static function (): void {
        Route::get('/', [StudentPermitController::class, 'index'])->name('index');
        Route::get('/{permit}', [StudentPermitController::class, 'show'])
            ->whereUlid('permit')
            ->name('show');
    });
