<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity Tahfidz yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Tahfidz/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe([
            'tahfidz.view',
            'tahfidz.manage',
            'tahfidz.record',
            'tahfidz.review',
            'tahfidz.archive',
        ])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('Tahfidz');
});

it('membedakan permission Tahfidz sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Tahfidz/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['tahfidz.view'])->toBeFalse()
        ->and($sensitive['tahfidz.manage'])->toBeTrue()
        ->and($sensitive['tahfidz.record'])->toBeTrue()
        ->and($sensitive['tahfidz.review'])->toBeTrue()
        ->and($sensitive['tahfidz.archive'])->toBeTrue();
});
