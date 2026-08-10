<?php

it('memuat package StarterKit dan provider Laravel', function () {
    $installed = dirname(__DIR__, 2).'/vendor/composer/installed.php';

    expect(class_exists('StarterKit\\StarterKitServiceProvider'))->toBeTrue()
        ->and(file_exists($installed))->toBeTrue()
        ->and(file_get_contents($installed))
        ->toContain('starterkit/framework');
});

it('menyediakan contract dan service utama framework package', function () {
    expect(class_exists('StarterKit\\Modules\\ModuleRegistry'))->toBeTrue()
        ->and(class_exists('StarterKit\\Modules\\Contracts\\ModuleManifest'))->toBeTrue()
        ->and(class_exists('StarterKit\\Modules\\Contracts\\PermissionIdentity'))->toBeTrue()
        ->and(class_exists('StarterKit\\Console\\Commands\\ModuleMakeCommand'))->toBeTrue();
});

it('memakai constraint PHP 8.4 pada package framework', function () {
    $package = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2).'/packages/StarterKit/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($package['require']['php'])->toBe('^8.4');
});
