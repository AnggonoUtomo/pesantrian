<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Presentation\Controllers\ClassGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('academic/class-groups')
    ->name('academic.class-groups.')
    ->group(function (): void {
        Route::post('/curricula', [ClassGroupController::class, 'storeCurriculum'])
            ->name('curricula.store');
        Route::patch('/curricula/{curriculum}', [ClassGroupController::class, 'updateCurriculum'])
            ->whereUlid('curriculum')
            ->name('curricula.update');
        Route::post('/levels', [ClassGroupController::class, 'storeLevel'])
            ->name('levels.store');
        Route::patch('/levels/{level}', [ClassGroupController::class, 'updateLevel'])
            ->whereUlid('level')
            ->name('levels.update');
        Route::get('/', [ClassGroupController::class, 'index'])->name('index');
        Route::post('/', [ClassGroupController::class, 'store'])->name('store');
        Route::patch('/{classGroup}', [ClassGroupController::class, 'update'])
            ->whereUlid('classGroup')
            ->name('update');
        Route::patch('/{classGroup}/archive', [ClassGroupController::class, 'archive'])
            ->whereUlid('classGroup')
            ->name('archive');
        Route::patch('/{classGroup}/restore', [ClassGroupController::class, 'restore'])
            ->whereUlid('classGroup')
            ->name('restore');
        Route::post('/{classGroup}/students', [ClassGroupController::class, 'storeStudent'])
            ->whereUlid('classGroup')
            ->name('students.store');
        Route::post('/{classGroup}/homerooms', [ClassGroupController::class, 'storeHomeroom'])
            ->whereUlid('classGroup')
            ->name('homerooms.store');
        Route::patch('/{classGroup}/homerooms/{homeroom}/end', [ClassGroupController::class, 'endHomeroom'])
            ->whereUlid('classGroup')
            ->whereUlid('homeroom')
            ->name('homerooms.end');
        Route::get('/{classGroup}', [ClassGroupController::class, 'show'])
            ->whereUlid('classGroup')
            ->name('show');
    });
