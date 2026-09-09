<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity PrestasiSantri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PrestasiSantri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe([
            'prestasi_santri.view',
            'prestasi_santri.manage',
            'prestasi_santri.record',
            'prestasi_santri.verify',
            'prestasi_santri.archive',
        ])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('PrestasiSantri');
});

it('membedakan permission PrestasiSantri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PrestasiSantri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['prestasi_santri.view'])->toBeFalse()
        ->and($sensitive['prestasi_santri.manage'])->toBeTrue()
        ->and($sensitive['prestasi_santri.record'])->toBeTrue()
        ->and($sensitive['prestasi_santri.verify'])->toBeTrue()
        ->and($sensitive['prestasi_santri.archive'])->toBeTrue();
});
