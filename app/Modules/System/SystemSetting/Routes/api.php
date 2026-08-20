<?php

declare(strict_types=1);

use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Presentation\Controllers\SystemSettingApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('api/v1/system-settings')
    ->name('api.v1.system-settings.')
    ->group(function (): void {
        Route::get('/', [SystemSettingApiController::class, 'index'])
            ->middleware('throttle:system-api')
            ->name('index');
        Route::patch('/{key}', [SystemSettingApiController::class, 'update'])
            ->where('key', '[A-Za-z0-9_.-]+')
            ->middleware([
                'throttle:system-api',
                'can:update,'.SystemSettingRecord::class,
                'api.idempotency',
            ])
            ->name('update');
    });
