<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use StarterKit\Modules\Contracts\ModuleManifest;
use StarterKit\Modules\ModuleRegistry;
use StarterKit\Modules\ModuleRuntimeState;

beforeEach(function () {
    File::deleteDirectory(app_path('Modules/System/InvalidModule'));
    File::deleteDirectory(app_path('Modules/System/DisabledModule'));
});

afterEach(function () {
    File::deleteDirectory(app_path('Modules/System/InvalidModule'));
    File::deleteDirectory(app_path('Modules/System/DisabledModule'));
});

it('menyediakan command module dengan output JSON', function () {
    $commands = collect(['module:discover', 'module:validate', 'module:list']);

    foreach ($commands as $command) {
        $exitCode = $this->artisan($command, ['--json' => true])->assertExitCode(0);
        expect($exitCode)->not->toBeNull();
    }
});

it('menginspeksi module target dengan output JSON tanpa side effect', function () {
    $manifestPath = app_path('Modules/System/AccessControl/module.json');
    $before = file_get_contents($manifestPath);

    $this->artisan('module:inspect', ['module' => 'System/AccessControl', '--json' => true])
        ->assertExitCode(0)
        ->expectsOutputToContain('MODULE_INSPECTED');

    expect(file_get_contents($manifestPath))->toBe($before);
});

it('mengembalikan failure JSON jika module target tidak ditemukan', function () {
    $this->artisan('module:inspect', ['module' => 'System/MissingModule', '--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_NOT_FOUND');
});

it('menolak format target module yang tidak valid', function () {
    $this->artisan('module:inspect', ['module' => 'AccessControl', '--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_INSPECTION_FAILED');
});

it('mengembalikan failure JSON saat discover menemukan module invalid', function () {
    createInvalidModuleFixture();

    $this->artisan('module:discover', ['--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_DISCOVERY_FAILED');

    expect(Artisan::output())->not->toContain(app_path('Modules'));
});

it('mengembalikan failure JSON saat validate menemukan module invalid', function () {
    createInvalidModuleFixture();

    $this->artisan('module:validate', ['--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_INVALID');
});

it('memvalidasi target module secara spesifik', function () {
    $this->artisan('module:validate', ['module' => 'System/AccessControl', '--json' => true])
        ->assertExitCode(0)
        ->expectsOutputToContain('MODULE_VALID');
});

it('mengembalikan failure saat target validate tidak ditemukan', function () {
    $this->artisan('module:validate', ['module' => 'System/MissingModule', '--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_TARGET_NOT_FOUND');
});

it('mengembalikan failure JSON saat list menemukan module invalid', function () {
    createInvalidModuleFixture();

    $this->artisan('module:list', ['--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_LIST_FAILED');
});

it('menampilkan boot plan production yang sama pada command discovery', function () {
    $exitCode = Artisan::call('module:discover', ['--json' => true]);
    $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload['data']['boot_plan'])->toBe([
            'AccessControl',
            'UserManagement',
            'AuditLog',
            'SystemSetting',
        ]);
});

it('memakai diagnostic graph canonical pada seluruh command module', function () {
    createDisabledModuleFixture();

    $commands = [
        ['module:discover', ['--json' => true]],
        ['module:validate', ['module' => 'System/DisabledModule', '--json' => true]],
        ['module:list', ['--json' => true]],
        ['module:inspect', ['module' => 'System/DisabledModule', '--json' => true]],
    ];

    foreach ($commands as [$command, $arguments]) {
        $exitCode = Artisan::call($command, $arguments);
        $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $diagnostic = collect($payload['diagnostics'])->firstWhere('module', 'DisabledModule');

        expect($exitCode)->toBe(1)
            ->and($diagnostic)->toMatchArray([
                'code' => 'module_disabled',
                'module' => 'DisabledModule',
                'phase' => 'validation',
                'message' => 'Module dinonaktifkan oleh manifest.',
            ]);
    }
});

it('memakai diagnostic runtime canonical pada seluruh command module', function () {
    $result = app(ModuleRegistry::class)->bootPlan(app_path('Modules'));
    $manifest = collect($result['boot_plan'])->first(
        static fn (ModuleManifest $module): bool => $module->name === 'AccessControl',
    );
    expect($manifest)->toBeInstanceOf(ModuleManifest::class);
    app(ModuleRuntimeState::class)->markBootFailed($manifest);

    $commands = [
        ['module:discover', ['--json' => true]],
        ['module:validate', ['module' => 'System/AccessControl', '--json' => true]],
        ['module:list', ['--json' => true]],
        ['module:inspect', ['module' => 'System/AccessControl', '--json' => true]],
    ];

    foreach ($commands as [$command, $arguments]) {
        $exitCode = Artisan::call($command, $arguments);
        $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        $diagnostic = collect($payload['diagnostics'])->firstWhere('module', 'AccessControl');

        expect($exitCode)->toBe(1)
            ->and($diagnostic)->toMatchArray([
                'code' => 'provider_boot_failed',
                'module' => 'AccessControl',
                'phase' => 'boot',
                'message' => 'Provider module gagal pada fase boot.',
            ]);
    }
});

function createInvalidModuleFixture(): void
{
    $modulePath = app_path('Modules/System/InvalidModule');
    File::ensureDirectoryExists($modulePath);
    File::put($modulePath.'/module.json', json_encode([
        'name' => 'InvalidModule',
        'namespace' => 'App\\Modules\\System\\InvalidModule',
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => 'active',
        'domain' => 'System',
        'path' => 'app/Modules/System/InvalidModule',
        'provider' => 'App\\Modules\\System\\InvalidModule\\ServiceProvider',
        'dependencies' => [],
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ], JSON_THROW_ON_ERROR));
}

function createDisabledModuleFixture(): void
{
    $modulePath = app_path('Modules/System/DisabledModule');
    File::ensureDirectoryExists($modulePath);
    File::put($modulePath.'/module.json', json_encode([
        'name' => 'DisabledModule',
        'namespace' => 'App\\Modules\\System\\DisabledModule',
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => 'disabled',
        'domain' => 'System',
        'path' => 'app/Modules/System/DisabledModule',
        'provider' => 'App\\Modules\\System\\DisabledModule\\ServiceProvider',
        'dependencies' => [],
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ], JSON_THROW_ON_ERROR));
    File::put($modulePath.'/permissions.php', '<?php return [];');
    File::put($modulePath.'/module.php', '<?php return [];');
    File::put($modulePath.'/ServiceProvider.php', <<<'PHP'
<?php

namespace App\Modules\System\DisabledModule;

final class ServiceProvider extends \Illuminate\Support\ServiceProvider {}
PHP);
}
