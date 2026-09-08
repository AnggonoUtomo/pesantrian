<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers\StudentDisciplineApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-discipline-categories')
    ->name('api.v1.pesantrian.student-discipline-categories.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineApiController::class, 'categories'])->name('index');
        Route::post('/', [StudentDisciplineApiController::class, 'storeCategory'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{category}', [StudentDisciplineApiController::class, 'updateCategory'])
            ->whereUlid('category')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{category}/archive', [StudentDisciplineApiController::class, 'archiveCategory'])
            ->whereUlid('category')
            ->middleware('api.idempotency')
            ->name('archive');
    });

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/student-discipline-cases')
    ->name('api.v1.pesantrian.student-discipline-cases.')
    ->group(static function (): void {
        Route::get('/', [StudentDisciplineApiController::class, 'index'])->name('index');
        Route::post('/', [StudentDisciplineApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{case}', [StudentDisciplineApiController::class, 'update'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{case}/submit', [StudentDisciplineApiController::class, 'submit'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('submit');
        Route::patch('/{case}/review', [StudentDisciplineApiController::class, 'review'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('review');
        Route::patch('/{case}/assign-action', [StudentDisciplineApiController::class, 'assignAction'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('assign-action');
        Route::patch('/{case}/resolve', [StudentDisciplineApiController::class, 'resolve'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('resolve');
        Route::patch('/{case}/void', [StudentDisciplineApiController::class, 'void'])
            ->whereUlid('case')
            ->middleware('api.idempotency')
            ->name('void');
        Route::get('/{case}', [StudentDisciplineApiController::class, 'show'])
            ->whereUlid('case')
            ->name('show');
    });
