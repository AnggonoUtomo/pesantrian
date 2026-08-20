<?php

declare(strict_types=1);

use App\Modules\System\SystemSetting\Application\Services\IdempotencyManager;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\IdempotencyKeyRecord;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories\EloquentIdempotencyRepository;
use App\Modules\System\SystemSetting\Presentation\Middleware\EnforceSystemSettingIdempotency;
use StarterKit\Http\Idempotency\Contracts\IdempotencyRepository;
use StarterKit\Http\Middleware\EnforceIdempotency;
use StarterKit\Modules\Contracts\PermissionIdentity;

it('menjaga ownership persistence idempotency pada SystemSetting dan mekanisme generik pada framework', function (): void {
    expect(class_exists(EnforceIdempotency::class))->toBeTrue()
        ->and(interface_exists(IdempotencyRepository::class))->toBeTrue()
        ->and(file_exists(app_path(
            'Modules/System/SystemSetting/Database/Migrations/2026_08_06_200000_create_system_setting_tables.php',
        )))->toBeTrue()
        ->and(class_exists(
            IdempotencyKeyRecord::class,
        ))->toBeTrue()
        ->and(class_exists(
            EloquentIdempotencyRepository::class,
        ))->toBeTrue()
        ->and(class_exists(
            IdempotencyManager::class,
        ))->toBeFalse()
        ->and(class_exists(
            EnforceSystemSettingIdempotency::class,
        ))->toBeFalse();
});

it('mendeklarasikan identity dan dependency SystemSetting', function (): void {
    $manifest = json_decode(
        file_get_contents(app_path('Modules/System/SystemSetting/module.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['name'])->toBe('SystemSetting')
        ->and($manifest['domain'])->toBe('System')
        ->and($manifest['dependencies'])->toBe(['AccessControl', 'UserManagement', 'AuditLog']);
});

it('memiliki permission identity SystemSetting yang unik dan sensitif', function (): void {
    $permissions = require app_path('Modules/System/SystemSetting/permissions.php');
    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['system_setting.view', 'system_setting.manage'])
        ->and(array_map(static fn (PermissionIdentity $permission): bool => $permission->sensitive, $identities))
        ->each->toBeTrue();
});
