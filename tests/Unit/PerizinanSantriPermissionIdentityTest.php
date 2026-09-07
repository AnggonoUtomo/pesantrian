<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity PerizinanSantri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PerizinanSantri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(6)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe([
            'perizinan_santri.view',
            'perizinan_santri.manage',
            'perizinan_santri.approve',
            'perizinan_santri.checkout',
            'perizinan_santri.return',
            'perizinan_santri.archive',
        ])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(6)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('PerizinanSantri');
});

it('membedakan permission PerizinanSantri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/PerizinanSantri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['perizinan_santri.view'])->toBeFalse()
        ->and($sensitive['perizinan_santri.manage'])->toBeTrue()
        ->and($sensitive['perizinan_santri.approve'])->toBeTrue()
        ->and($sensitive['perizinan_santri.checkout'])->toBeTrue()
        ->and($sensitive['perizinan_santri.return'])->toBeTrue()
        ->and($sensitive['perizinan_santri.archive'])->toBeTrue();
});
