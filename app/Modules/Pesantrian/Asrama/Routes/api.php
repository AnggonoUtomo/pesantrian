<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitoryApiController;
use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitorySupervisorApiController;
use App\Modules\Pesantrian\Asrama\Presentation\Controllers\StudentRoomPlacementApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/pesantrian/asrama')
    ->name('api.v1.pesantrian.asrama.')
    ->group(function (): void {
        Route::get('/', [DormitoryApiController::class, 'index'])->name('index');
        Route::post('/', [DormitoryApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{dormitory}', [DormitoryApiController::class, 'update'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('update');
        Route::patch('/{dormitory}/archive', [DormitoryApiController::class, 'archive'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('archive');
        Route::patch('/{dormitory}/restore', [DormitoryApiController::class, 'restore'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('restore');
        Route::post('/{dormitory}/rooms', [DormitoryApiController::class, 'storeRoom'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('rooms.store');
        Route::patch('/{dormitory}/rooms/{room}', [DormitoryApiController::class, 'updateRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->middleware('api.idempotency')
            ->name('rooms.update');
        Route::patch('/{dormitory}/rooms/{room}/archive', [DormitoryApiController::class, 'archiveRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->middleware('api.idempotency')
            ->name('rooms.archive');
        Route::patch('/{dormitory}/rooms/{room}/restore', [DormitoryApiController::class, 'restoreRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->middleware('api.idempotency')
            ->name('rooms.restore');
        Route::post('/{dormitory}/supervisors', [DormitorySupervisorApiController::class, 'store'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('supervisors.store');
        Route::patch('/{dormitory}/supervisors/{assignment}/end', [DormitorySupervisorApiController::class, 'end'])
            ->whereUlid('dormitory')
            ->whereUlid('assignment')
            ->middleware('api.idempotency')
            ->name('supervisors.end');
        Route::post('/{dormitory}/placements', [StudentRoomPlacementApiController::class, 'store'])
            ->whereUlid('dormitory')
            ->middleware('api.idempotency')
            ->name('placements.store');
        Route::patch('/{dormitory}/placements/{placement}/transfer', [StudentRoomPlacementApiController::class, 'transfer'])
            ->whereUlid('dormitory')
            ->whereUlid('placement')
            ->middleware('api.idempotency')
            ->name('placements.transfer');
        Route::patch('/{dormitory}/placements/{placement}/remove', [StudentRoomPlacementApiController::class, 'remove'])
            ->whereUlid('dormitory')
            ->whereUlid('placement')
            ->middleware('api.idempotency')
            ->name('placements.remove');
        Route::get('/{dormitory}', [DormitoryApiController::class, 'show'])
            ->whereUlid('dormitory')
            ->name('show');
    });
