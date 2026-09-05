<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/asrama')
    ->name('pesantrian.asrama.')
    ->group(function (): void {
        Route::get('/', [DormitoryController::class, 'index'])->name('index');
        Route::get('/{dormitory}', [DormitoryController::class, 'show'])
            ->whereUlid('dormitory')
            ->name('show');
    });
