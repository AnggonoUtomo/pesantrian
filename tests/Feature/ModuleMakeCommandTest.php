<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->originalModuleMakeAppPath = app_path();
    $this->originalModuleMakeStoragePath = storage_path();
    $this->moduleMakeRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starterkit-module-make-'.Str::ulid()->toBase32();
    $this->moduleMakeAppPath = $this->moduleMakeRoot.DIRECTORY_SEPARATOR.'app';
    $this->app->useAppPath($this->moduleMakeAppPath);
    $this->app->useStoragePath($this->moduleMakeRoot.DIRECTORY_SEPARATOR.'storage');

    cleanupGeneratorProbes();
});

afterEach(function () {
    cleanupGeneratorProbes();
    File::deleteDirectory($this->moduleMakeRoot);
    $this->app->useAppPath($this->originalModuleMakeAppPath);
    $this->app->useStoragePath($this->originalModuleMakeStoragePath);
});

it('menyediakan dry-run JSON tanpa membuat file', function () {
    $result = runModuleMake('Billing', ['--domain=System', '--dry-run', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_PREVIEWED');

    expect(File::exists(app_path('Modules/System/Billing')))->toBeFalse();
});

it('membuat module baru dan mengembalikan JSON sukses', function () {
    $result = runModuleMake('GeneratorCreateProbe', ['--domain=System', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_CREATED');

    expect(File::exists(app_path('Modules/System/GeneratorCreateProbe/module.json')))->toBeTrue();
});

it('menolak input invalid sebelum membuat file', function () {
    $result = runModuleMake('invalid-module', ['--domain=System', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_INVALID');

    expect(File::exists(app_path('Modules/System/invalid-module')))->toBeFalse();
});

it('menolak mutasi tanpa konfirmasi force', function () {
    $result = runModuleMake('AuditLog', ['--domain=System', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_INVALID')
        ->and($result->getOutput())->toContain('--force');

    expect(File::exists(app_path('Modules/System/AuditLog')))->toBeFalse();
});

it('mengembalikan failure saat target conflict', function () {
    $initial = runModuleMake('GeneratorConflictProbe', ['--domain=System', '--force', '--yes']);

    expect($initial->getExitCode())->toBe(0, $initial->getOutput());

    $result = runModuleMake('GeneratorConflictProbe', ['--domain=System', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_FAILED');
});

it('mengizinkan extension additive tanpa overwrite', function () {
    expect(runModuleMake('GeneratorExtensionProbe', ['--domain=System', '--force', '--yes', '--json'])->getExitCode())->toBe(0);
    File::put(app_path('Modules/System/GeneratorExtensionProbe/custom.txt'), 'keep');

    $result = runModuleMake('GeneratorExtensionProbe', ['--domain=System', '--extension', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_CREATED')
        ->and(File::get(app_path('Modules/System/GeneratorExtensionProbe/custom.txt')))->toBe('keep');
});

it('menolak overwrite tanpa extension sebelum mutation', function () {
    expect(runModuleMake('GeneratorOverwriteGuardProbe', ['--domain=System', '--force', '--yes', '--json'])->getExitCode())->toBe(0);
    File::put(app_path('Modules/System/GeneratorOverwriteGuardProbe/module.php'), 'custom');

    $result = runModuleMake('GeneratorOverwriteGuardProbe', ['--domain=System', '--overwrite', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('overwrite membutuhkan extension')
        ->and(File::get(app_path('Modules/System/GeneratorOverwriteGuardProbe/module.php')))->toBe('custom');
});

it('mengizinkan overwrite file plan dengan guard lengkap', function () {
    expect(runModuleMake('GeneratorOverwriteProbe', ['--domain=System', '--force', '--yes', '--json'])->getExitCode())->toBe(0);
    File::put(app_path('Modules/System/GeneratorOverwriteProbe/module.php'), 'custom');

    $result = runModuleMake('GeneratorOverwriteProbe', ['--domain=System', '--extension', '--overwrite', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_CREATED')
        ->and(File::get(app_path('Modules/System/GeneratorOverwriteProbe/module.php')))->toContain('return []');
});

/** @param list<string> $options */
function runModuleMake(string $module, array $options): ModuleMakeCommandResult
{
    $arguments = ['module' => $module];

    foreach ($options as $option) {
        [$name, $value] = array_pad(explode('=', $option, 2), 2, true);
        $arguments[$name] = $value;
    }

    $exitCode = Artisan::call('module:make', $arguments);

    return new ModuleMakeCommandResult($exitCode, Artisan::output());
}

final readonly class ModuleMakeCommandResult
{
    public function __construct(
        private int $exitCode,
        private string $output,
    ) {}

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}

function cleanupGeneratorProbes(): void
{
    foreach ([
        'GeneratorCreateProbe',
        'GeneratorConflictProbe',
        'GeneratorExtensionProbe',
        'GeneratorOverwriteGuardProbe',
        'GeneratorOverwriteProbe',
    ] as $module) {
        File::deleteDirectory(app_path('Modules/System/'.$module));
    }
}
