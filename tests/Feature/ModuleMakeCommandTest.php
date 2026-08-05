<?php

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

beforeEach(function () {
    File::deleteDirectory(app_path('Modules/System/AuditLog'));
});

afterEach(function () {
    File::deleteDirectory(app_path('Modules/System/AuditLog'));
});

it('menyediakan dry-run JSON tanpa membuat file', function () {
    $result = runModuleMake('Billing', ['--domain=System', '--dry-run', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_PREVIEWED');

    expect(File::exists(app_path('Modules/System/Billing')))->toBeFalse();
});

it('membuat module baru dan mengembalikan JSON sukses', function () {
    $result = runModuleMake('AuditLog', ['--domain=System', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(0)
        ->and($result->getOutput())->toContain('MODULE_CREATED');

    expect(File::exists(app_path('Modules/System/AuditLog/module.json')))->toBeTrue();
});

it('menolak input invalid sebelum membuat file', function () {
    $result = runModuleMake('invalid-module', ['--domain=System', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_INVALID');

    expect(File::exists(app_path('Modules/System/AccessControl')))->toBeTrue();
});

it('menolak mutasi tanpa konfirmasi force', function () {
    $result = runModuleMake('AuditLog', ['--domain=System', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_INVALID')
        ->and($result->getOutput())->toContain('--force');

    expect(File::exists(app_path('Modules/System/AccessControl')))->toBeTrue();
});

it('mengembalikan failure saat target conflict', function () {
    expect(runModuleMake('AuditLog', ['--domain=System', '--force', '--yes'])->getExitCode())->toBe(0);

    $result = runModuleMake('AuditLog', ['--domain=System', '--force', '--yes', '--json']);

    expect($result->getExitCode())->toBe(1)
        ->and($result->getOutput())->toContain('MODULE_GENERATION_FAILED');
});

/** @param list<string> $options */
function runModuleMake(string $module, array $options): Process
{
    $process = new Process([
        PHP_BINARY,
        base_path('artisan'),
        'module:make',
        $module,
        ...$options,
    ], base_path());
    $process->run();

    return $process;
}
