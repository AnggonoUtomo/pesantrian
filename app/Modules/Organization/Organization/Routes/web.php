<?php

declare(strict_types=1);

use App\Modules\Organization\Organization\Presentation\Controllers\OrganizationUnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('organization/units')
    ->name('organization.units.')
    ->group(function (): void {
        Route::get('/', [OrganizationUnitController::class, 'index'])->name('index');
    });
