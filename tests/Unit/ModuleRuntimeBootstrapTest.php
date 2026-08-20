<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use StarterKit\Modules\ModuleRuntimeServiceProvider;
use StarterKit\Modules\ModuleRuntimeState;
use Tests\Fixtures\RuntimeModules\BootFailure\ServiceProvider as BootFailureServiceProvider;
use Tests\Fixtures\RuntimeModules\Dependent\ServiceProvider as DependentServiceProvider;
use Tests\Fixtures\RuntimeModules\Disabled\ServiceProvider as DisabledServiceProvider;
use Tests\Fixtures\RuntimeModules\Independent\ServiceProvider as IndependentServiceProvider;
use Tests\Fixtures\RuntimeModules\RegisterFailure\ServiceProvider as RegisterFailureServiceProvider;
use Tests\Fixtures\RuntimeModules\Root\ServiceProvider as RootServiceProvider;

require_once dirname(__DIR__).'/Fixtures/RuntimeModules/RuntimeServiceProviders.php';

/** @param list<string> $dependencies */
function writeBootstrapModuleFixture(
    string $root,
    string $name,
    string $provider,
    array $dependencies = [],
    string $status = 'enabled',
): void {
    $modulePath = $root.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR.$name;
    mkdir($modulePath, 0755, true);
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.json', json_encode([
        'name' => $name,
        'namespace' => "Tests\\Fixtures\\RuntimeModules\\{$name}",
        'version' => '1.0.0',
        'schema_version' => 1,
        'status' => $status,
        'domain' => 'System',
        'path' => "app/Modules/System/{$name}",
        'provider' => $provider,
        'dependencies' => $dependencies,
        'permission_source' => 'permissions.php',
        'config_source' => 'module.php',
    ], JSON_THROW_ON_ERROR));
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'permissions.php', '<?php return [];');
    file_put_contents($modulePath.DIRECTORY_SEPARATOR.'module.php', '<?php return [];');
}

function removeBootstrapModuleFixture(string $root): void
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

function bootFixtureApplication(string $root): Application
{
    $application = new Application(dirname(__DIR__, 2));
    $application->instance('starterkit.modules.path', $root);
    $application->register(ModuleRuntimeServiceProvider::class);
    $application->boot();

    return $application;
}

/** @return list<string> */
function bootstrapRuntimeTrace(Application $application): array
{
    return $application->bound('module-runtime-trace')
        ? $application->make('module-runtime-trace')
        : [];
}

it('hanya register dan boot module valid enabled', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-runtime-'.Str::ulid();
    writeBootstrapModuleFixture($root, 'Root', RootServiceProvider::class);
    writeBootstrapModuleFixture($root, 'Independent', IndependentServiceProvider::class);
    writeBootstrapModuleFixture($root, 'Disabled', DisabledServiceProvider::class, status: 'disabled');
    writeBootstrapModuleFixture($root, 'InvalidProvider', 'Tests\\Fixtures\\RuntimeModules\\Missing\\ServiceProvider');

    try {
        $application = bootFixtureApplication($root);
        $state = $application->make(ModuleRuntimeState::class);

        expect(bootstrapRuntimeTrace($application))->toBe([
            'independent.register',
            'root.register',
            'independent.boot',
            'root.boot',
        ])->and($state->status('Root'))->toBe('booted')
            ->and($state->status('Disabled'))->toBe('isolated')
            ->and(collect($state->diagnostics())->pluck('code')->all())->toContain(
                'module_disabled',
                'provider_invalid',
            );
    } finally {
        removeBootstrapModuleFixture($root);
    }
});

it('mengisolasi register failure dan dependent tanpa menjatuhkan peer', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-runtime-'.Str::ulid();
    writeBootstrapModuleFixture($root, 'RegisterFailure', RegisterFailureServiceProvider::class);
    writeBootstrapModuleFixture($root, 'Dependent', DependentServiceProvider::class, ['RegisterFailure']);
    writeBootstrapModuleFixture($root, 'Independent', IndependentServiceProvider::class);

    try {
        $application = bootFixtureApplication($root);
        $state = $application->make(ModuleRuntimeState::class);
        $diagnostics = json_encode($state->diagnostics(), JSON_THROW_ON_ERROR);

        expect(bootstrapRuntimeTrace($application))->toBe([
            'independent.register',
            'independent.boot',
        ])->and($state->status('RegisterFailure'))->toBe('register_failed')
            ->and($state->status('Dependent'))->toBe('isolated')
            ->and($diagnostics)->toContain('provider_register_failed')
            ->and($diagnostics)->toContain('dependency_runtime_unavailable')
            ->and($diagnostics)->not->toContain('credential-register-fixture');
    } finally {
        removeBootstrapModuleFixture($root);
    }
});

it('mengisolasi boot failure dan dependent tanpa menjatuhkan peer', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-runtime-'.Str::ulid();
    writeBootstrapModuleFixture($root, 'BootFailure', BootFailureServiceProvider::class);
    writeBootstrapModuleFixture($root, 'Dependent', DependentServiceProvider::class, ['BootFailure']);
    writeBootstrapModuleFixture($root, 'Independent', IndependentServiceProvider::class);

    try {
        $application = bootFixtureApplication($root);
        $state = $application->make(ModuleRuntimeState::class);
        $diagnostics = json_encode($state->diagnostics(), JSON_THROW_ON_ERROR);

        expect(bootstrapRuntimeTrace($application))->toBe([
            'boot-failure.register',
            'dependent.register',
            'independent.register',
            'independent.boot',
        ])->and($state->status('BootFailure'))->toBe('boot_failed')
            ->and($state->status('Dependent'))->toBe('isolated')
            ->and($state->status('Independent'))->toBe('booted')
            ->and($diagnostics)->toContain('provider_boot_failed')
            ->and($diagnostics)->toContain('dependency_runtime_unavailable')
            ->and($diagnostics)->not->toContain('credential-boot-fixture');
    } finally {
        removeBootstrapModuleFixture($root);
    }
});
