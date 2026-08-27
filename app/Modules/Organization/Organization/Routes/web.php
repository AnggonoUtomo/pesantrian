<?php

declare(strict_types=1);

use App\Modules\Organization\Organization\Presentation\Controllers\OrganizationUnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('organization/units')
    ->name('organization.units.')
    ->group(function (): void {
        Route::get('/', [OrganizationUnitController::class, 'index'])->name('index');
        Route::post('/', [OrganizationUnitController::class, 'store'])->name('store');
        Route::put('/{unit}', [OrganizationUnitController::class, 'update'])
            ->whereUlid('unit')
            ->name('update');
        Route::patch('/{unit}/archive', [OrganizationUnitController::class, 'archive'])
            ->whereUlid('unit')
            ->name('archive');
        Route::patch('/{unit}/restore', [OrganizationUnitController::class, 'restore'])
            ->whereUlid('unit')
            ->name('restore');
    });
