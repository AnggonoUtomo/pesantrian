<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('memiliki permission identity AcademicPeriod yang valid dan unik', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Academic/AcademicPeriod/permissions.php';

    $identities = array_map(
        static fn (array $permission): PermissionIdentity => PermissionIdentity::fromArray($permission),
        $permissions,
    );

    expect($identities)->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities))
        ->toBe(['academic_period.view', 'academic_period.manage'])
        ->and(array_unique(array_map(static fn (PermissionIdentity $permission): string => $permission->key, $identities)))
        ->toHaveCount(2)
        ->and(array_map(static fn (PermissionIdentity $permission): string => $permission->module, $identities))
        ->each->toBe('AcademicPeriod');
});

it('membedakan permission AcademicPeriod sensitif dan non-sensitif', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/Academic/AcademicPeriod/permissions.php';

    $sensitive = array_column($permissions, 'sensitive', 'key');

    expect($sensitive['academic_period.view'])->toBeFalse()
        ->and($sensitive['academic_period.manage'])->toBeTrue();
});
