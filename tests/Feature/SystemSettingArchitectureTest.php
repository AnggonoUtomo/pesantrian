<?php

declare(strict_types=1);

use StarterKit\Modules\Contracts\PermissionIdentity;

it('mendeklarasikan identity dan dependency SystemSetting', function (): void {
    $manifest = json_decode(
        file_get_contents(app_path('Modules/System/SystemSetting/module.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['name'])->toBe('SystemSetting')
        ->and($manifest['domain'])->toBe('System')
        ->and($manifest['dependencies'])->toBe(['AccessControl', 'AuditLog']);
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
