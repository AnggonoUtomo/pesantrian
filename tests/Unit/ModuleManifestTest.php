<?php

use StarterKit\Modules\Contracts\ModuleManifest;

it('membuat manifest dari data valid', function () {
    $manifest = ModuleManifest::fromArray([
        'name' => 'AccessControl',
        'namespace' => 'App\\Modules\\System\\AccessControl',
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => 'enabled',
        'domain' => 'System',
        'path' => 'app/Modules/System/AccessControl',
        'provider' => 'App\\Modules\\System\\AccessControl\\ServiceProvider',
        'dependencies' => [],
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ]);

    expect($manifest->name)->toBe('AccessControl');
});

it('menolak field manifest yang hilang', function () {
    $thrown = false;

    try {
        ModuleManifest::fromArray(['name' => 'AccessControl']);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'namespace');
    }

    expect($thrown)->toBeTrue();
});

it('menolak status manifest yang tidak dikenal', function () {
    $data = [
        'name' => 'AccessControl', 'namespace' => 'App\\Modules\\System\\AccessControl',
        'version' => '1.0.0', 'schema_version' => 1, 'status' => 'active',
        'domain' => 'System', 'path' => 'app/Modules/System/AccessControl',
        'provider' => 'App\\Modules\\System\\AccessControl\\ServiceProvider',
        'dependencies' => [], 'permission_source' => 'permissions.php', 'config_source' => 'module.php',
    ];
    $thrown = false;

    try {
        ModuleManifest::fromArray($data);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'status');
    }

    expect($thrown)->toBeTrue();
});

it('menolak namespace manifest yang tidak valid', function () {
    $data = [
        'name' => 'AccessControl', 'namespace' => 'app\\Modules\\System\\AccessControl',
        'version' => '1.0.0', 'schema_version' => 1, 'status' => 'enabled',
        'domain' => 'System', 'path' => 'app/Modules/System/AccessControl',
        'provider' => 'App\\Modules\\System\\AccessControl\\ServiceProvider',
        'dependencies' => [], 'permission_source' => 'permissions.php', 'config_source' => 'module.php',
    ];
    $thrown = false;

    try {
        ModuleManifest::fromArray($data);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'namespace');
    }

    expect($thrown)->toBeTrue();
});

it('menolak path manifest yang tidak valid', function () {
    $data = [
        'name' => 'AccessControl', 'namespace' => 'App\\Modules\\System\\AccessControl',
        'version' => '1.0.0', 'schema_version' => 1, 'status' => 'enabled',
        'domain' => 'System', 'path' => 'modules/System/AccessControl',
        'provider' => 'App\\Modules\\System\\AccessControl\\ServiceProvider',
        'dependencies' => [], 'permission_source' => 'permissions.php', 'config_source' => 'module.php',
    ];
    $thrown = false;

    try {
        ModuleManifest::fromArray($data);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'path');
    }

    expect($thrown)->toBeTrue();
});

it('menolak provider manifest yang tidak berakhiran ServiceProvider', function () {
    $data = [
        'name' => 'AccessControl', 'namespace' => 'App\\Modules\\System\\AccessControl',
        'version' => '1.0.0', 'schema_version' => 1, 'status' => 'enabled',
        'domain' => 'System', 'path' => 'app/Modules/System/AccessControl',
        'provider' => 'App\\Modules\\System\\AccessControl\\Provider',
        'dependencies' => [], 'permission_source' => 'permissions.php', 'config_source' => 'module.php',
    ];
    $thrown = false;

    try {
        ModuleManifest::fromArray($data);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), 'provider');
    }

    expect($thrown)->toBeTrue();
});
