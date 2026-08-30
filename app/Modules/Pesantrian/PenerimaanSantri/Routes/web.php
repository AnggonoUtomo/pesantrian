<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Controllers\StudentAdmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/admissions')
    ->name('pesantrian.admissions.')
    ->group(function (): void {
        Route::get('/', [StudentAdmissionController::class, 'index'])->name('index');
    });
