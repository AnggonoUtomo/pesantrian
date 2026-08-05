<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(app_path('Modules/System/InvalidModule'));
});

afterEach(function () {
    File::deleteDirectory(app_path('Modules/System/InvalidModule'));
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
