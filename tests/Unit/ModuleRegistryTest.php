<?php

use Illuminate\Support\Str;
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

it('mengisolasi module saat config source tidak tersedia', function () {
    $result = (new ModuleRegistry)->discover(dirname(__DIR__).'/Fixtures/Modules/MissingSource');

    expect($result['modules'])->toBe([])
        ->and($result['diagnostics'][0]['message'])->toContain('Config source');
});

it('menemukan module pada struktur nested secara recursive', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-registry-'.Str::ulid();
    $modulePath = $root.DIRECTORY_SEPARATOR.'nested'.DIRECTORY_SEPARATOR.'scope'.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR.'AuditLog';

    mkdir($modulePath, 0755, true);
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.json', json_encode([
        'name' => 'AuditLog',
        'namespace' => 'App\\Modules\\System\\AuditLog',
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => 'enabled',
        'domain' => 'System',
        'path' => 'app/Modules/System/AuditLog',
        'provider' => 'App\\Modules\\System\\AuditLog\\ServiceProvider',
        'dependencies' => [],
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ], JSON_THROW_ON_ERROR));
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'permissions.php', '<?php return [];');
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.php', '<?php return [];');

    try {
        $result = (new ModuleRegistry)->discover($root);

        expect($result['modules'])->toHaveCount(1)
            ->and($result['modules'][0]->name)->toBe('AuditLog');
    } finally {
        @unlink($modulePath.DIRECTORY_SEPARATOR.'module.json');
        @unlink($modulePath.DIRECTORY_SEPARATOR.'permissions.php');
        @unlink($modulePath.DIRECTORY_SEPARATOR.'module.php');
        rmdir($modulePath);
        rmdir(dirname($modulePath));
        rmdir(dirname(dirname($modulePath)));
        rmdir(dirname(dirname(dirname($modulePath))));
        rmdir($root);
    }
});
