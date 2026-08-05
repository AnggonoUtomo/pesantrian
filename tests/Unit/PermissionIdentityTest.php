<?php

use StarterKit\Modules\Contracts\PermissionIdentity;

it('membuat permission identity dari data valid', function () {
    $permission = PermissionIdentity::fromArray([
        'key' => 'access_control.role.manage',
        'description' => 'Manage roles',
        'module' => 'AccessControl',
        'sensitive' => false,
    ]);

    expect($permission->key)->toBe('access_control.role.manage')
        ->and($permission->module)->toBe('AccessControl')
        ->and($permission->sensitive)->toBeFalse();
});

it('menolak field permission yang hilang', function () {
    $thrown = false;

    try {
        PermissionIdentity::fromArray(['key' => 'access_control.role.manage']);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'description');
    }

    expect($thrown)->toBeTrue();
});

it('menolak permission key yang bukan dot notation', function () {
    $thrown = false;

    try {
        PermissionIdentity::fromArray([
            'key' => 'AccessControl Role Manage',
            'description' => 'Manage roles',
            'module' => 'AccessControl',
            'sensitive' => false,
        ]);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'dot notation');
    }

    expect($thrown)->toBeTrue();
});

it('menolak description permission yang kosong', function () {
    $thrown = false;

    try {
        PermissionIdentity::fromArray([
            'key' => 'access_control.role.manage',
            'description' => '   ',
            'module' => 'AccessControl',
            'sensitive' => false,
        ]);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'description');
    }

    expect($thrown)->toBeTrue();
});

it('menolak module permission yang bukan PascalCase', function () {
    $thrown = false;

    try {
        PermissionIdentity::fromArray([
            'key' => 'access_control.role.manage',
            'description' => 'Manage roles',
            'module' => 'access_control',
            'sensitive' => false,
        ]);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'PascalCase');
    }

    expect($thrown)->toBeTrue();
});

it('menolak sensitive permission yang bukan boolean', function () {
    $thrown = false;

    try {
        PermissionIdentity::fromArray([
            'key' => 'access_control.role.manage',
            'description' => 'Manage roles',
            'module' => 'AccessControl',
            'sensitive' => 'false',
        ]);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'boolean');
    }

    expect($thrown)->toBeTrue();
});
