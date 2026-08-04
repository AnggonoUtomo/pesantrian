<?php

declare(strict_types=1);

namespace StarterKit;

use Illuminate\Support\ServiceProvider;
use StarterKit\Console\Commands\ModuleDiscoverCommand;
use StarterKit\Console\Commands\ModuleListCommand;
use StarterKit\Console\Commands\ModuleValidateCommand;
use StarterKit\Modules\ModuleRegistry;

final class StarterKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleDiscoverCommand::class,
                ModuleValidateCommand::class,
                ModuleListCommand::class,
            ]);
        }
    }
}
