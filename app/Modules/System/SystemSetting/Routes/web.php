<?php

declare(strict_types=1);

use App\Modules\System\SystemSetting\Presentation\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('system/system-settings')
    ->name('system.system-settings.')
    ->group(function (): void {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::patch('/{key}', [SystemSettingController::class, 'update'])
            ->where('key', '[A-Za-z0-9_.-]+')
            ->name('update');
    });
