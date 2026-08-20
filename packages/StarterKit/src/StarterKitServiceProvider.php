<?php

declare(strict_types=1);

namespace StarterKit;

use Illuminate\Support\ServiceProvider;
use StarterKit\Console\Commands\ModuleDiscoverCommand;
use StarterKit\Console\Commands\ModuleInspectCommand;
use StarterKit\Console\Commands\ModuleListCommand;
use StarterKit\Console\Commands\ModuleMakeCommand;
use StarterKit\Console\Commands\ModuleValidateCommand;
use StarterKit\Http\Idempotency\Contracts\IdempotencyRepository;
use StarterKit\Http\Idempotency\Contracts\RuntimeApiPolicy;
use StarterKit\Http\Idempotency\Runtime\DefaultRuntimeApiPolicy;
use StarterKit\Http\Idempotency\Runtime\UnavailableIdempotencyRepository;
use StarterKit\Modules\ModuleRegistry;

final class StarterKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(RuntimeApiPolicy::class, DefaultRuntimeApiPolicy::class);
        $this->app->singleton(IdempotencyRepository::class, UnavailableIdempotencyRepository::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleDiscoverCommand::class,
                ModuleValidateCommand::class,
                ModuleListCommand::class,
                ModuleInspectCommand::class,
                ModuleMakeCommand::class,
            ]);
        }
    }
}
