<?php

use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\Profiles\DefaultModuleProfile;

it('menghasilkan plan default-v1 dengan struktur canonical', function () {
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'namespace' => 'Console',
    ]);

    $plan = (new DefaultModuleProfile)->plan($request);

    expect($plan->profile)->toBe('default-v1')
        ->and($plan->targetPath)->toBe('app/Modules/Console/AccessControl')
        ->and($plan->directories)->toBe([])
        ->and(array_keys($plan->files))->toContain('module.json', 'ServiceProvider.php', 'README.md')
        ->and($plan->files['module.json'])->toContain('App\\\\Modules\\\\Console\\\\AccessControl')
        ->and($plan->files['ServiceProvider.php'])->toContain('namespace App\\Modules\\Console\\AccessControl;')
        ->and($plan->files['ServiceProvider.php'])->toContain('use Illuminate\\Support\\ServiceProvider as FrameworkServiceProvider;')
        ->and($plan->files['ServiceProvider.php'])->toContain('final class ServiceProvider extends FrameworkServiceProvider');
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
