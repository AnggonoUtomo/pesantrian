<?php

declare(strict_types=1);

use App\Modules\System\SystemSetting\Presentation\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('system/system-settings')
    ->name('system.system-settings.')
    ->group(function (): void {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::patch('/categories/{category}', [SystemSettingController::class, 'updateCategory'])
            ->where('category', 'api|password|session|mail|pagination|branding|monitoring|operations')
            ->name('category.update');
        Route::patch('/{key}', [SystemSettingController::class, 'update'])
            ->where('key', '[A-Za-z0-9_.-]+')
            ->name('update');
    });
