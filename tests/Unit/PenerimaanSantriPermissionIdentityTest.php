<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity PenerimaanSantri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PenerimaanSantri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(3)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['penerimaan_santri.view', 'penerimaan_santri.manage', 'penerimaan_santri.decide'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(3)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('PenerimaanSantri');
});

it('membedakan permission PenerimaanSantri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PenerimaanSantri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['penerimaan_santri.view'])->toBeFalse()
        ->and($sensitive['penerimaan_santri.manage'])->toBeTrue()
        ->and($sensitive['penerimaan_santri.decide'])->toBeTrue();
});
