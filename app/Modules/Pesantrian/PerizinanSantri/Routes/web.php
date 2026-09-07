<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers\StudentPermitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/student-permits')
    ->name('pesantrian.student-permits.')
    ->group(static function (): void {
        Route::get('/', [StudentPermitController::class, 'index'])->name('index');
        Route::post('/', [StudentPermitController::class, 'store'])->name('store');
        Route::patch('/{permit}', [StudentPermitController::class, 'update'])
            ->whereUlid('permit')
            ->name('update');
        Route::patch('/{permit}/submit', [StudentPermitController::class, 'submit'])
            ->whereUlid('permit')
            ->name('submit');
        Route::patch('/{permit}/approve', [StudentPermitController::class, 'approve'])
            ->whereUlid('permit')
            ->name('approve');
        Route::patch('/{permit}/reject', [StudentPermitController::class, 'reject'])
            ->whereUlid('permit')
            ->name('reject');
        Route::patch('/{permit}/checkout', [StudentPermitController::class, 'checkout'])
            ->whereUlid('permit')
            ->name('checkout');
        Route::patch('/{permit}/return', [StudentPermitController::class, 'returnPermit'])
            ->whereUlid('permit')
            ->name('return');
        Route::patch('/{permit}/void', [StudentPermitController::class, 'void'])
            ->whereUlid('permit')
            ->name('void');
        Route::get('/{permit}', [StudentPermitController::class, 'show'])
            ->whereUlid('permit')
            ->name('show');
    });
