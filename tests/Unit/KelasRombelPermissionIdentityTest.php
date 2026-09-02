<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity KelasRombel yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Academic/KelasRombel/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(4)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['kelas_rombel.view', 'kelas_rombel.manage', 'kelas_rombel.placement', 'kelas_rombel.archive'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(4)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('KelasRombel');
});

it('membedakan permission KelasRombel sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Academic/KelasRombel/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['kelas_rombel.view'])->toBeFalse()
        ->and($sensitive['kelas_rombel.manage'])->toBeTrue()
        ->and($sensitive['kelas_rombel.placement'])->toBeTrue()
        ->and($sensitive['kelas_rombel.archive'])->toBeTrue();
});
