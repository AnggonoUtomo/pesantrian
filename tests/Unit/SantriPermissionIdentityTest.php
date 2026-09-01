<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity Santri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Santri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(4)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['santri.view', 'santri.manage', 'santri.lifecycle', 'santri.archive'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(4)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('Santri');
});

it('membedakan permission Santri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Santri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['santri.view'])->toBeFalse()
        ->and($sensitive['santri.manage'])->toBeTrue()
        ->and($sensitive['santri.lifecycle'])->toBeTrue()
        ->and($sensitive['santri.archive'])->toBeTrue();
});
