<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Presentation\Controllers\ClassGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('academic/class-groups')
    ->name('academic.class-groups.')
    ->group(function (): void {
        Route::get('/', [ClassGroupController::class, 'index'])->name('index');
        Route::get('/{classGroup}', [ClassGroupController::class, 'show'])
            ->whereUlid('classGroup')
            ->name('show');
    });
