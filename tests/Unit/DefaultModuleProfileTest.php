<?php

use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\Profiles\DefaultModuleProfile;

it('menghasilkan plan default-v1 dengan struktur canonical', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
    ]);

    $plan = (new DefaultModuleProfile)->plan($request);

    expect($plan->profile)->toBe('default-v1')
        ->and($plan->targetPath)->toBe('app/Modules/System/AccessControl')
        ->and($plan->directories)->toContain('Application/Actions')
        ->and($plan->directories)->toContain('Domain/ValueObjects')
        ->and($plan->directories)->toContain('Infrastructure/Persistence/Models')
        ->and($plan->directories)->toContain('Tests/Integration')
        ->and(array_keys($plan->files))->toContain('module.json', 'ServiceProvider.php', 'README.md')
        ->and($plan->files['module.json'])->toContain('App\\\\Modules\\\\System\\\\AccessControl')
        ->and($plan->files['ServiceProvider.php'])->toContain('namespace App\\Modules\\System\\AccessControl;');
});

it('menghasilkan plan deterministik untuk input yang sama', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'UserManagement',
        'domain' => 'System',
    ]);
    $profile = new DefaultModuleProfile;

    expect($profile->plan($request))->toEqual($profile->plan($request));
});

it('menolak profile yang tidak didukung oleh default profile', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
        'profile' => 'platform-v2',
    ]);

    expect(fn () => (new DefaultModuleProfile)->plan($request))
        ->toThrow(InvalidArgumentException::class, 'default-v1');
});
