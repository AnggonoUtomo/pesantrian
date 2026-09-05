<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Presentation\Controllers\DormitoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('pesantrian/asrama')
    ->name('pesantrian.asrama.')
    ->group(function (): void {
        Route::get('/', [DormitoryController::class, 'index'])->name('index');
        Route::post('/', [DormitoryController::class, 'store'])->name('store');
        Route::get('/{dormitory}', [DormitoryController::class, 'show'])
            ->whereUlid('dormitory')
            ->name('show');
        Route::patch('/{dormitory}', [DormitoryController::class, 'update'])
            ->whereUlid('dormitory')
            ->name('update');
        Route::patch('/{dormitory}/archive', [DormitoryController::class, 'archive'])
            ->whereUlid('dormitory')
            ->name('archive');
        Route::patch('/{dormitory}/restore', [DormitoryController::class, 'restore'])
            ->whereUlid('dormitory')
            ->name('restore');
        Route::post('/{dormitory}/rooms', [DormitoryController::class, 'storeRoom'])
            ->whereUlid('dormitory')
            ->name('rooms.store');
        Route::patch('/{dormitory}/rooms/{room}', [DormitoryController::class, 'updateRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->name('rooms.update');
        Route::patch('/{dormitory}/rooms/{room}/archive', [DormitoryController::class, 'archiveRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->name('rooms.archive');
        Route::patch('/{dormitory}/rooms/{room}/restore', [DormitoryController::class, 'restoreRoom'])
            ->whereUlid('dormitory')
            ->whereUlid('room')
            ->name('rooms.restore');
        Route::post('/{dormitory}/placements', [DormitoryController::class, 'storePlacement'])
            ->whereUlid('dormitory')
            ->name('placements.store');
        Route::patch('/{dormitory}/placements/{placement}/transfer', [DormitoryController::class, 'transferPlacement'])
            ->whereUlid('dormitory')
            ->whereUlid('placement')
            ->name('placements.transfer');
        Route::patch('/{dormitory}/placements/{placement}/remove', [DormitoryController::class, 'removePlacement'])
            ->whereUlid('dormitory')
            ->whereUlid('placement')
            ->name('placements.remove');
        Route::post('/{dormitory}/supervisors', [DormitoryController::class, 'storeSupervisor'])
            ->whereUlid('dormitory')
            ->name('supervisors.store');
        Route::patch('/{dormitory}/supervisors/{assignment}/end', [DormitoryController::class, 'endSupervisor'])
            ->whereUlid('dormitory')
            ->whereUlid('assignment')
            ->name('supervisors.end');
    });
