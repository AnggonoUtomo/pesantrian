<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Santri\Presentation\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/students')
    ->name('pesantrian.students.')
    ->group(function (): void {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/{student}', [StudentController::class, 'show'])
            ->whereUlid('student')
            ->name('show');
    });
