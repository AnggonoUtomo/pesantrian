<?php

use App\Modules\System\SystemSetting\ServiceProvider;
use Illuminate\Support\Str;
use StarterKit\Modules\ModuleRegistry;

/**
 * @param  list<string>  $dependencies
 * @param  class-string|string  $provider
 */
function writeRuntimeModuleFixture(
    string $root,
    string $name,
    string $provider,
    array $dependencies = [],
    string $status = 'enabled',
): void {
    $modulePath = $root.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR.$name;
    mkdir($modulePath, 0755, true);
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.json', json_encode([
        'name' => $name,
        'namespace' => "App\\Modules\\System\\{$name}",
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => $status,
        'domain' => 'System',
        'path' => "app/Modules/System/{$name}",
        'provider' => $provider,
        'dependencies' => $dependencies,
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ], JSON_THROW_ON_ERROR));
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'permissions.php', '<?php return [];');
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.php', '<?php return [];');
}

function removeRuntimeModuleFixture(string $root): void
{
    if (! is_dir($root)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($root);
}

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

it('membuat boot plan topological yang stabil', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'SystemSetting', ServiceProvider::class, ['AccessControl', 'AuditLog']);
    writeRuntimeModuleFixture($root, 'AuditLog', App\Modules\System\AuditLog\ServiceProvider::class, ['AccessControl', 'UserManagement']);
    writeRuntimeModuleFixture($root, 'UserManagement', App\Modules\System\UserManagement\ServiceProvider::class, ['AccessControl']);
    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);

        expect(collect($result['boot_plan'])->pluck('name')->all())->toBe([
            'AccessControl',
            'UserManagement',
            'AuditLog',
            'SystemSetting',
        ])->and($result['diagnostics'])->toBe([]);
    } finally {
        removeRuntimeModuleFixture($root);
    }
});

it('mengisolasi dependency hilang dan mempertahankan peer independen', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class, ['MissingModule']);
    writeRuntimeModuleFixture($root, 'SystemSetting', ServiceProvider::class);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);

        expect(collect($result['boot_plan'])->pluck('name')->all())->toBe(['SystemSetting'])
            ->and(collect($result['diagnostics'])->contains(
                fn (array $diagnostic): bool => $diagnostic['code'] === 'dependency_missing'
                    && $diagnostic['module'] === 'AccessControl'
                    && $diagnostic['phase'] === 'validation',
            ))->toBeTrue();
    } finally {
        removeRuntimeModuleFixture($root);
    }
});

it('mengisolasi cycle dependency dan dependent-nya', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'UserManagement', App\Modules\System\UserManagement\ServiceProvider::class, ['AuditLog']);
    writeRuntimeModuleFixture($root, 'AuditLog', App\Modules\System\AuditLog\ServiceProvider::class, ['UserManagement']);
    writeRuntimeModuleFixture($root, 'SystemSetting', ServiceProvider::class, ['AuditLog']);
    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);
        $cycleModules = collect($result['diagnostics'])
            ->where('code', 'dependency_cycle')
            ->pluck('module')
            ->sort()
            ->values()
            ->all();

        expect(collect($result['boot_plan'])->pluck('name')->all())->toBe(['AccessControl'])
            ->and($cycleModules)->toBe(['AuditLog', 'UserManagement'])
            ->and(collect($result['diagnostics'])->contains(
                fn (array $diagnostic): bool => $diagnostic['code'] === 'dependency_unavailable'
                    && $diagnostic['module'] === 'SystemSetting',
            ))->toBeTrue();
    } finally {
        removeRuntimeModuleFixture($root);
    }
});

it('mengisolasi module disabled beserta dependent-nya', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class, status: 'disabled');
    writeRuntimeModuleFixture($root, 'UserManagement', App\Modules\System\UserManagement\ServiceProvider::class, ['AccessControl']);
    writeRuntimeModuleFixture($root, 'SystemSetting', ServiceProvider::class);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);

        expect(collect($result['boot_plan'])->pluck('name')->all())->toBe(['SystemSetting'])
            ->and(collect($result['diagnostics'])->pluck('code')->all())->toContain(
                'module_disabled',
                'dependency_unavailable',
            );
    } finally {
        removeRuntimeModuleFixture($root);
    }
});

it('mengisolasi provider invalid tanpa membocorkan nama class', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'BrokenProvider', 'App\\Modules\\System\\BrokenProvider\\ServiceProvider');
    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);
        $diagnostic = collect($result['diagnostics'])->firstWhere('code', 'provider_invalid');

        expect(collect($result['boot_plan'])->pluck('name')->all())->toBe(['AccessControl'])
            ->and($diagnostic)->toMatchArray([
                'module' => 'BrokenProvider',
                'phase' => 'validation',
                'message' => 'Provider module tidak tersedia atau tidak valid.',
            ])->and(json_encode($diagnostic, JSON_THROW_ON_ERROR))->not->toContain('App\\Modules');
    } finally {
        removeRuntimeModuleFixture($root);
    }
});

it('menolak self dependency dengan diagnostic stabil', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-graph-'.Str::ulid();

    writeRuntimeModuleFixture($root, 'AccessControl', App\Modules\System\AccessControl\ServiceProvider::class, ['AccessControl']);

    try {
        $result = (new ModuleRegistry)->bootPlan($root);

        expect($result['boot_plan'])->toBe([])
            ->and($result['diagnostics'])->toHaveCount(1)
            ->and($result['diagnostics'][0])->toMatchArray([
                'code' => 'dependency_self',
                'module' => 'AccessControl',
                'phase' => 'validation',
            ]);
    } finally {
        removeRuntimeModuleFixture($root);
    }
});
