<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use Illuminate\Support\ServiceProvider;
use StarterKit\Modules\Contracts\ModuleManifest;
use Throwable;

final class ModuleRuntimeServiceProvider extends ServiceProvider
{
    /** @var array<string, ServiceProvider> */
    private array $providers = [];

    /** @var array<string, ModuleManifest> */
    private array $manifests = [];

    private ModuleRuntimeState $state;

    public function register(): void
    {
        $this->state = new ModuleRuntimeState;
        $this->app->instance(ModuleRuntimeState::class, $this->state);

        $registry = $this->app->bound(ModuleRegistry::class)
            ? $this->app->make(ModuleRegistry::class)
            : new ModuleRegistry;
        $rootPath = $this->app->bound('starterkit.modules.path')
            ? (string) $this->app->make('starterkit.modules.path')
            : $this->app->path('Modules');
        $result = $registry->bootPlan($rootPath);
        $this->state->initialize($result['modules'], $result['boot_plan'], $result['diagnostics']);

        foreach ($result['boot_plan'] as $manifest) {
            if (! $this->state->dependenciesRegistered($manifest->dependencies)) {
                $this->state->isolateDependency($manifest, 'register');

                continue;
            }

            try {
                $provider = $this->makeProvider($manifest->provider);

                $provider->register();
                $this->applyProviderBindings($provider);
                $this->providers[$manifest->name] = $provider;
                $this->manifests[$manifest->name] = $manifest;
                $this->state->markRegistered($manifest->name);
            } catch (Throwable) {
                $this->state->markRegisterFailed($manifest);
            }
        }
    }

    public function boot(): void
    {
        foreach ($this->manifests as $name => $manifest) {
            if (! $this->state->dependenciesBooted($manifest->dependencies)) {
                $this->state->isolateDependency($manifest, 'boot');

                continue;
            }

            try {
                $provider = $this->providers[$name];
                $provider->callBootingCallbacks();

                if (method_exists($provider, 'boot')) {
                    $this->app->call([$provider, 'boot']);
                }

                $provider->callBootedCallbacks();
                $this->state->markBooted($name);
            } catch (Throwable) {
                $this->state->markBootFailed($manifest);
            }
        }
    }

    private function makeProvider(string $providerClass): ServiceProvider
    {
        if (! is_subclass_of($providerClass, ServiceProvider::class)) {
            throw new \RuntimeException;
        }

        return new $providerClass($this->app);
    }

    private function applyProviderBindings(ServiceProvider $provider): void
    {
        $properties = get_object_vars($provider);

        foreach ($properties['bindings'] ?? [] as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }

        foreach ($properties['singletons'] ?? [] as $abstract => $concrete) {
            $abstract = is_int($abstract) ? $concrete : $abstract;
            $this->app->singleton($abstract, $concrete);
        }
    }
}
