<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(app_path('Modules'));
});

afterEach(function () {
    File::deleteDirectory(app_path('Modules'));
});

it('menyediakan command module dengan output JSON', function () {
    $commands = collect(['module:discover', 'module:validate', 'module:list']);

    foreach ($commands as $command) {
        $exitCode = $this->artisan($command, ['--json' => true])->assertExitCode(0);
        expect($exitCode)->not->toBeNull();
    }
});

it('mengembalikan failure JSON saat discover menemukan module invalid', function () {
    createInvalidModuleFixture();

    $this->artisan('module:discover', ['--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_DISCOVERY_FAILED');
});

it('mengembalikan failure JSON saat validate menemukan module invalid', function () {
    createInvalidModuleFixture();

    $this->artisan('module:validate', ['--json' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('MODULE_INVALID');
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
