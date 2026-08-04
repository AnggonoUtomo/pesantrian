<?php

use StarterKit\Generator\Contracts\ModuleGenerationRequest;

it('membuat request generator valid dengan default profile', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
    ]);

    expect($request->module)->toBe('AccessControl')
        ->and($request->domain)->toBe('System')
        ->and($request->profile)->toBe('default-v1')
        ->and($request->dryRun)->toBeFalse()
        ->and($request->force)->toBeFalse()
        ->and($request->yes)->toBeFalse();
});

it('menerima opsi profile dan mode generator', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'UserManagement',
        'domain' => 'System',
        'profile' => 'platform-v2',
        'dry_run' => true,
        'force' => true,
        'yes' => true,
    ]);

    expect($request->profile)->toBe('platform-v2')
        ->and($request->dryRun)->toBeTrue()
        ->and($request->force)->toBeTrue()
        ->and($request->yes)->toBeTrue();
});

it('menolak nama module, domain, dan profile yang tidak aman', function (array $data, string $message) {
    expect(fn () => ModuleGenerationRequest::fromArray($data))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'module' => [['module' => 'invalid-module', 'domain' => 'System'], 'module'],
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
