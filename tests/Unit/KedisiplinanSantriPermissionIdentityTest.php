<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity KedisiplinanSantri yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/KedisiplinanSantri/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe([
            'kedisiplinan_santri.view',
            'kedisiplinan_santri.manage',
            'kedisiplinan_santri.review',
            'kedisiplinan_santri.resolve',
            'kedisiplinan_santri.archive',
        ])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('KedisiplinanSantri');
});

it('membedakan permission KedisiplinanSantri sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/KedisiplinanSantri/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['kedisiplinan_santri.view'])->toBeFalse()
        ->and($sensitive['kedisiplinan_santri.manage'])->toBeTrue()
        ->and($sensitive['kedisiplinan_santri.review'])->toBeTrue()
        ->and($sensitive['kedisiplinan_santri.resolve'])->toBeTrue()
        ->and($sensitive['kedisiplinan_santri.archive'])->toBeTrue();
});
