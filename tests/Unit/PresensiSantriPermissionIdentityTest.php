<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity PresensiSantri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PresensiSantri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe([
            'presensi_santri.view',
            'presensi_santri.manage',
            'presensi_santri.submit',
            'presensi_santri.revise',
            'presensi_santri.archive',
        ])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('PresensiSantri');
});

it('membedakan permission PresensiSantri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PresensiSantri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['presensi_santri.view'])->toBeFalse()
        ->and($sensitive['presensi_santri.manage'])->toBeTrue()
        ->and($sensitive['presensi_santri.submit'])->toBeTrue()
        ->and($sensitive['presensi_santri.revise'])->toBeTrue()
        ->and($sensitive['presensi_santri.archive'])->toBeTrue();
});
