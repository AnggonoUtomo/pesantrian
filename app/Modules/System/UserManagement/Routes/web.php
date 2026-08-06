<?php

declare(strict_types=1);

use App\Modules\System\UserManagement\Presentation\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('system/users')
    ->name('system.users.')
    ->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/impersonation/leave', [UserController::class, 'leaveImpersonation'])->name('impersonation.leave');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::patch('/{user}/status', [UserController::class, 'changeStatus'])->name('status');
        Route::patch('/{user}/roles', [UserController::class, 'assignRole'])->name('roles');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('restore');
        Route::delete('/{user}/force', [UserController::class, 'forceDelete'])->withTrashed()->name('force-delete');
        Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])->name('impersonate');
    });
