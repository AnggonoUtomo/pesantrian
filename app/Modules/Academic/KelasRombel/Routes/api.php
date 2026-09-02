<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Presentation\Controllers\ClassGroupApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/academic/class-groups')
    ->name('api.v1.academic.class-groups.')
    ->group(function (): void {
        Route::get('/', [ClassGroupApiController::class, 'index'])->name('index');
        Route::get('/{classGroup}', [ClassGroupApiController::class, 'show'])
            ->whereUlid('classGroup')
            ->name('show');
    });
