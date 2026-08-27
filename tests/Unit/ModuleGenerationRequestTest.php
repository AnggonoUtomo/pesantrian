<?php

use StarterKit\Generator\Contracts\ModuleGenerationRequest;

it('membuat request generator valid dengan default profile', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
    ]);

    expect($request->module)->toBe('AccessControl')
        ->and($request->namespace)->toBe('System')
        ->and($request->domain)->toBe('System')
        ->and($request->profile)->toBe('default-v1')
        ->and($request->dryRun)->toBeFalse()
        ->and($request->force)->toBeFalse()
        ->and($request->yes)->toBeFalse()
        ->and($request->extension)->toBeFalse()
        ->and($request->overwrite)->toBeFalse();
});

it('menerima namespace sebagai istilah baseline dan domain sebagai alias kompatibilitas', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'Student',
        'namespace' => 'StudentLife',
    ]);

    expect($request->module)->toBe('Student')
        ->and($request->namespace)->toBe('StudentLife')
        ->and($request->domain)->toBe('StudentLife');
});

it('menerima opsi profile dan mode generator', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'UserManagement',
        'domain' => 'System',
        'profile' => 'platform-v2',
        'dry_run' => true,
        'force' => true,
        'yes' => true,
        'extension' => true,
        'overwrite' => true,
    ]);

    expect($request->profile)->toBe('platform-v2')
        ->and($request->dryRun)->toBeTrue()
        ->and($request->force)->toBeTrue()
        ->and($request->yes)->toBeTrue()
        ->and($request->extension)->toBeTrue()
        ->and($request->overwrite)->toBeTrue();
});

it('menolak nama module, domain, dan profile yang tidak aman', function (array $data, string $message) {
    expect(fn () => ModuleGenerationRequest::fromArray($data))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'module' => [['module' => 'invalid-module', 'domain' => 'System'], 'module'],
    'namespace' => [['module' => 'Student', 'namespace' => '../StudentLife'], 'namespace'],
    'domain' => [['module' => 'AccessControl', 'domain' => '../System'], 'domain'],
    'profile' => [['module' => 'AccessControl', 'domain' => 'System', 'profile' => '../default'], 'profile'],
]);

it('menolak yes tanpa force', function () {
    expect(fn () => ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
        'yes' => true,
    ]))->toThrow(InvalidArgumentException::class, 'yes membutuhkan force');
});

it('menolak overwrite tanpa guard extension force dan yes', function (array $data, string $message) {
    expect(fn () => ModuleGenerationRequest::fromArray(array_merge([
        'module' => 'AccessControl',
        'domain' => 'System',
        'overwrite' => true,
    ], $data)))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'extension' => [['extension' => false, 'force' => true, 'yes' => true], 'overwrite membutuhkan extension'],
    'force' => [['extension' => true, 'force' => false, 'yes' => false], 'overwrite membutuhkan force'],
    'yes' => [['extension' => true, 'force' => true, 'yes' => false], 'overwrite membutuhkan yes'],
]);
