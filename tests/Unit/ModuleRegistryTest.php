<?php

use StarterKit\Modules\ModuleRegistry;

it('mengembalikan hasil kosong saat folder module belum ada', function () {
    $result = (new ModuleRegistry)->discover(dirname(__DIR__, 2).'\\missing-modules');

    expect($result['modules'])->toBe([])
        ->and($result['diagnostics'])->toBe([]);
});

it('menemukan module valid dan mengisolasi module invalid', function () {
    $result = (new ModuleRegistry)->discover(dirname(__DIR__).'/Fixtures/Modules/Basic');

    expect($result['modules'])->toHaveCount(1)
        ->and($result['modules'][0]->name)->toBe('AccessControl')
        ->and($result['diagnostics'])->toHaveCount(1)
        ->and($result['diagnostics'][0]['message'])->toContain('status');
});

it('mendeteksi duplicate module identity', function () {
    $result = (new ModuleRegistry)->discover(dirname(__DIR__).'/Fixtures/Modules/Duplicate');

    expect($result['diagnostics'])->not->toBe([])
        ->and(collect($result['diagnostics'])->pluck('message')->join(' '))->toContain('Duplicate module');
});

it('mendeteksi duplicate permission key antar module', function () {
    $result = (new ModuleRegistry)->discover(dirname(__DIR__).'/Fixtures/Modules/PermissionDuplicate');

    expect($result['modules'])->toHaveCount(1)
        ->and(collect($result['diagnostics'])->pluck('message')->join(' '))
        ->toContain('Duplicate permission key');
});
