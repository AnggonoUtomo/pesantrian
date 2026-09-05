<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity Asrama yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Asrama/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['asrama.view', 'asrama.manage', 'asrama.placement', 'asrama.supervisor', 'asrama.archive'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(5)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('Asrama');
});

it('membedakan permission Asrama sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Pesantrian/Asrama/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['asrama.view'])->toBeFalse()
        ->and($sensitive['asrama.manage'])->toBeTrue()
        ->and($sensitive['asrama.placement'])->toBeTrue()
        ->and($sensitive['asrama.supervisor'])->toBeTrue()
        ->and($sensitive['asrama.archive'])->toBeTrue();
});
