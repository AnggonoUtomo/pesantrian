<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity AccessControl yang valid dan unik', function () {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/System/AccessControl/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5);

    expect(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->toHaveCount(5)
        ->each->toBe('AccessControl');
});

it('membedakan permission sensitif dan non-sensitif', function () {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/System/AccessControl/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['access_control.role.manage'])->toBeFalse()
        ->and($sensitive['access_control.permission.manage'])->toBeTrue()
        ->and($sensitive['access_control.role.assign'])->toBeTrue()
        ->and($sensitive['access_control.permission.assign'])->toBeTrue();
});
