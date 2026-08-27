<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity Organization yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Organization/Organization/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['organization.view', 'organization.manage'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('Organization');
});

it('membedakan permission Organization sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Organization/Organization/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['organization.view'])->toBeFalse()
        ->and($sensitive['organization.manage'])->toBeTrue();
});
