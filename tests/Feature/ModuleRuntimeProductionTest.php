<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\ServiceProvider as AccessControlServiceProvider;
use App\Modules\System\AuditLog\ServiceProvider as AuditLogServiceProvider;
use App\Modules\System\SystemSetting\ServiceProvider as SystemSettingServiceProvider;
use App\Modules\System\UserManagement\ServiceProvider as UserManagementServiceProvider;
use StarterKit\Modules\ModuleRuntimeServiceProvider;
use StarterKit\Modules\ModuleRuntimeState;

it('menggunakan satu composition provider untuk seluruh module runtime', function (): void {
    $providers = require dirname(__DIR__, 2).'/bootstrap/providers.php';

    expect($providers)->toContain(ModuleRuntimeServiceProvider::class)
        ->not->toContain(
            AccessControlServiceProvider::class,
            UserManagementServiceProvider::class,
            AuditLogServiceProvider::class,
            SystemSettingServiceProvider::class,
        );
});

it('boot seluruh module production sesuai manifest tanpa diagnostic', function (): void {
    $state = app(ModuleRuntimeState::class);

    expect($state->diagnostics())->toBe([])
        ->and($state->status('AccessControl'))->toBe('booted')
        ->and($state->status('UserManagement'))->toBe('booted')
        ->and($state->status('AuditLog'))->toBe('booted')
        ->and($state->status('SystemSetting'))->toBe('booted');
});
