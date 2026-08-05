<?php

use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\ModuleConflictDetector;
use StarterKit\Generator\ModuleGenerationPreviewer;
use StarterKit\Generator\Profiles\DefaultModuleProfile;
use StarterKit\Modules\ModuleRegistry;

function generationPreviewer(): ModuleGenerationPreviewer
{
    return new ModuleGenerationPreviewer(
        new DefaultModuleProfile,
        new ModuleConflictDetector(new ModuleRegistry),
    );
}

it('menghasilkan preview valid tanpa menulis filesystem', function () {
    $rootPath = dirname(__DIR__).'/Fixtures/Modules/Empty';
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AuditLog',
        'domain' => 'System',
        'dry_run' => true,
    ]);

    $preview = generationPreviewer()->preview($request, $rootPath);

    expect($preview->isValid())->toBeTrue()
        ->and($preview->plan->targetPath)->toBe('app/Modules/System/AuditLog')
        ->and(is_dir($rootPath.'/System/AuditLog'))->toBeFalse();
});

it('mendeteksi target existing dan duplicate identity sebelum write', function () {
    $rootPath = dirname(__DIR__).'/Fixtures/Modules/Basic';
    $request = ModuleGenerationRequest::fromArray([
        'module' => 'AccessControl',
        'domain' => 'System',
        'dry_run' => true,
    ]);

    $preview = generationPreviewer()->preview($request, $rootPath);
    $codes = collect($preview->diagnostics)->pluck('code');

    expect($preview->isValid())->toBeFalse()
        ->and($codes)->toContain('TARGET_EXISTS')
        ->and($codes)->toContain('DUPLICATE_NAME');
});

it('menolak plan target di luar app Modules', function () {
    $plan = new ModuleGenerationPlan(
        'default-v1',
        'storage/escape',
        [],
        ['module.json' => '{}'],
    );

    expect(fn () => (new ModuleConflictDetector(new ModuleRegistry))->detect($plan, 'app/Modules'))
        ->toThrow(InvalidArgumentException::class, 'app/Modules');
});
