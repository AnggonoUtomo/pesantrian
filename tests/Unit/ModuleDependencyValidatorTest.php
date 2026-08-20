<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use StarterKit\Modules\Contracts\ModuleManifest;
use StarterKit\Modules\ModuleDependencyValidator;
use StarterKit\Modules\ModuleRegistry;

function dependencyValidatorManifest(string $name, array $dependencies = []): ModuleManifest
{
    return new ModuleManifest(
        name: $name,
        namespace: "App\\Modules\\System\\{$name}",
        version: '1.0.0',
        schemaVersion: 1,
        status: 'enabled',
        domain: 'System',
        path: "app/Modules/System/{$name}",
        provider: "App\\Modules\\System\\{$name}\\ServiceProvider",
        dependencies: $dependencies,
        permissionSource: 'permissions.php',
        configSource: 'module.php',
    );
}

function removeDependencyValidatorFixture(string $root): void
{
    if (! is_dir($root)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($root);
}

it('menerima import public yang dideklarasikan manifest', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'dependency-validator-'.Str::ulid();
    $modulePath = $root.'/System/Consumer/Application';
    mkdir($modulePath, 0755, true);
    file_put_contents(
        $modulePath.'/UsesOwner.php',
        '<?php use App\\Modules\\System\\Owner\\Application\\Contracts\\OwnerCapability;',
    );

    try {
        $diagnostics = (new ModuleDependencyValidator)->validate($root, [
            dependencyValidatorManifest('Owner'),
            dependencyValidatorManifest('Consumer', ['Owner']),
        ]);

        expect($diagnostics)->toBe([]);
    } finally {
        removeDependencyValidatorFixture($root);
    }
});

it('menolak import yang tidak dideklarasikan dan boundary private', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'dependency-validator-'.Str::ulid();
    $undeclaredPath = $root.'/System/Undeclared/Application';
    $privatePath = $root.'/System/PrivateConsumer/Application';
    mkdir($undeclaredPath, 0755, true);
    mkdir($privatePath, 0755, true);
    file_put_contents(
        $undeclaredPath.'/UsesOwner.php',
        '<?php use App\\Modules\\System\\Owner\\Application\\Contracts\\OwnerCapability;',
    );
    file_put_contents(
        $privatePath.'/UsesOwner.php',
        '<?php use App\\Modules\\System\\Owner\\Infrastructure\\Persistence\\Models\\OwnerRecord;',
    );

    try {
        $diagnostics = (new ModuleDependencyValidator)->validate($root, [
            dependencyValidatorManifest('Owner'),
            dependencyValidatorManifest('Undeclared'),
            dependencyValidatorManifest('PrivateConsumer', ['Owner']),
        ]);

        expect(collect($diagnostics)->pluck('code')->all())->toBe([
            'dependency_private',
            'dependency_undeclared',
        ])->and($diagnostics)->each->toHaveKeys([
            'code',
            'module',
            'phase',
            'path',
            'message',
        ]);
    } finally {
        removeDependencyValidatorFixture($root);
    }
});

it('memastikan import dan graph production acyclic tanpa diagnostic', function (): void {
    $registry = new ModuleRegistry;
    $result = $registry->bootPlan(dirname(__DIR__, 2).'/app/Modules');
    $diagnostics = (new ModuleDependencyValidator)->validate(
        dirname(__DIR__, 2).'/app/Modules',
        $result['modules'],
    );

    expect($diagnostics)->toBe([])
        ->and($result['diagnostics'])->toBe([])
        ->and(collect($result['boot_plan'])->pluck('name')->all())->toBe([
            'AccessControl',
            'UserManagement',
            'AuditLog',
            'SystemSetting',
        ]);
});
