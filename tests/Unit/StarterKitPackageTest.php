<?php

it('memuat package StarterKit dan provider Laravel', function () {
    $installed = dirname(__DIR__, 2).'/vendor/composer/installed.php';

    expect(class_exists('StarterKit\\StarterKitServiceProvider'))->toBeTrue()
        ->and(file_exists($installed))->toBeTrue()
        ->and(file_get_contents($installed))
        ->toContain('starterkit/framework');
});
