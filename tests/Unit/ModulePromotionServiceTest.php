<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\ModulePromotionService;
use StarterKit\Generator\Profiles\DefaultModuleProfile;

function promotionPlan(): ModuleGenerationPlan
{
    return (new DefaultModuleProfile)->plan(ModuleGenerationRequest::fromArray([
        'module' => 'AuditLog',
        'domain' => 'System',
    ]));
}

function testFilesystem(): Filesystem
{
    return new Filesystem;
}

it('mempromosikan output dari staging secara atomic', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starterkit-task005-'.Str::ulid();
    $rootPath = $base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';
    $stagingRoot = $base.DIRECTORY_SEPARATOR.'staging';
    testFilesystem()->ensureDirectoryExists($rootPath);

    try {
        $result = (new ModulePromotionService)->promote(promotionPlan(), $rootPath, $stagingRoot);
        $targetPath = $rootPath.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR.'AuditLog';

        expect($result->targetPath)->toBe($targetPath)
            ->and(is_file($targetPath.DIRECTORY_SEPARATOR.'module.json'))->toBeTrue()
            ->and(testFilesystem()->directories($stagingRoot))->toBe([]);
    } finally {
        testFilesystem()->deleteDirectory($base);
    }
});

it('membersihkan staging saat path output tidak aman', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starterkit-task005-'.Str::ulid();
    $rootPath = $base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';
    $stagingRoot = $base.DIRECTORY_SEPARATOR.'staging';
    testFilesystem()->ensureDirectoryExists($rootPath);
    $plan = new ModuleGenerationPlan('default-v1', 'app/Modules/System/AuditLog', [], ['../escape.php' => 'unsafe']);

    try {
        expect(fn () => (new ModulePromotionService)->promote($plan, $rootPath, $stagingRoot))
            ->toThrow(InvalidArgumentException::class, 'tidak aman')
            ->and(testFilesystem()->directories($stagingRoot))->toBe([]);
    } finally {
        testFilesystem()->deleteDirectory($base);
    }
});

it('menolak promotion ke target existing tanpa overwrite', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starterkit-task005-'.Str::ulid();
    $rootPath = $base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';
    $stagingRoot = $base.DIRECTORY_SEPARATOR.'staging';
    $targetPath = $rootPath.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR.'AuditLog';
    testFilesystem()->ensureDirectoryExists($targetPath);
    testFilesystem()->put($targetPath.'/keep.txt', 'keep');

    try {
        expect(fn () => (new ModulePromotionService)->promote(promotionPlan(), $rootPath, $stagingRoot))
            ->toThrow(RuntimeException::class, 'sudah ada')
            ->and(testFilesystem()->get($targetPath.'/keep.txt'))->toBe('keep');
    } finally {
        testFilesystem()->deleteDirectory($base);
    }
});
