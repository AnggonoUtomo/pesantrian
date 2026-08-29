<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity HumanResource yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/HumanResource/HumanResource/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['human_resource.view', 'human_resource.manage'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('HumanResource');
});

it('membedakan permission HumanResource sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/HumanResource/HumanResource/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['human_resource.view'])->toBeFalse()
        ->and($sensitive['human_resource.manage'])->toBeTrue();
});
