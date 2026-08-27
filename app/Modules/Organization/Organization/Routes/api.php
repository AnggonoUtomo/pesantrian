<?php

declare(strict_types=1);

use App\Modules\Organization\Organization\Presentation\Controllers\OrganizationUnitApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'throttle:system-api'])
    ->prefix('api/v1/organization/units')
    ->name('api.v1.organization.units.')
    ->group(function (): void {
        Route::get('/', [OrganizationUnitApiController::class, 'index'])->name('index');
        Route::post('/', [OrganizationUnitApiController::class, 'store'])
            ->middleware('api.idempotency')
            ->name('store');
        Route::patch('/{unit}', [OrganizationUnitApiController::class, 'update'])
            ->whereUlid('unit')
            ->middleware('api.idempotency')
            ->name('update');
    });
