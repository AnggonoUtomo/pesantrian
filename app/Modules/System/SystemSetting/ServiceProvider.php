<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting;

use App\Modules\System\SystemSetting\Application\Contracts\ExternalMonitoringCapability;
use App\Modules\System\SystemSetting\Application\Contracts\IdempotencyRepository;
use App\Modules\System\SystemSetting\Application\Contracts\SettingDefinitionRegistrar;
use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\Services\DatabaseSystemSettingReader;
use App\Modules\System\SystemSetting\Application\Services\ReadSystemRuntimeSettings;
use App\Modules\System\SystemSetting\Application\Services\RequestSettingMemoizer;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Infrastructure\Monitoring\UnavailableExternalMonitoringCapability;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories\EloquentIdempotencyRepository;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories\EloquentSystemSettingRepository;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\GetSystemSettingCommand;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\ListSystemSettingsCommand;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\PruneIdempotencyKeysCommand;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\SetSystemSettingCommand;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\ShowSystemRuntimeCommand;
use App\Modules\System\SystemSetting\Presentation\Console\Commands\ValidateSystemSettingsCommand;
use App\Modules\System\SystemSetting\Presentation\Policies\SystemSettingPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/module.php', 'system-setting');

        $this->app->singleton(SettingDefinitionRegistry::class, function (Application $app): SettingDefinitionRegistry {
            $config = $app->make(ConfigRepository::class);

            return new SettingDefinitionRegistry((string) $config->get('app.name', 'Laravel'));
        });
        $this->app->alias(SettingDefinitionRegistry::class, SettingDefinitionRegistrar::class);

        $this->app->bind(SystemSettingRepository::class, EloquentSystemSettingRepository::class);
        $this->app->bind(IdempotencyRepository::class, EloquentIdempotencyRepository::class);
        $this->app->singleton(
            ExternalMonitoringCapability::class,
            UnavailableExternalMonitoringCapability::class,
        );
        $this->app->scoped(RequestSettingMemoizer::class);
        $this->app->scoped(DatabaseSystemSettingReader::class);
        $this->app->alias(DatabaseSystemSettingReader::class, SystemSettingReader::class);
        $this->app->scoped(ReadSystemRuntimeSettings::class);
        $this->app->alias(ReadSystemRuntimeSettings::class, SystemRuntimeSettings::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        Gate::policy(SystemSettingRecord::class, SystemSettingPolicy::class);
        RateLimiter::for('system-api', function (Request $request): Limit {
            $actor = (string) $request->user()?->getAuthIdentifier();
            $endpoint = $request->route()?->getName() ?? $request->path();

            return Limit::perMinute(app(SystemSettingReader::class)->integer('api.rate_limit.per_minute'))
                ->by(($actor !== '' ? $actor : $request->ip()).'|'.$endpoint);
        });
        $this->commands([
            ListSystemSettingsCommand::class,
            GetSystemSettingCommand::class,
            SetSystemSettingCommand::class,
            ValidateSystemSettingsCommand::class,
            ShowSystemRuntimeCommand::class,
            PruneIdempotencyKeysCommand::class,
        ]);

        Schedule::command('system-setting:idempotency-prune')
            ->hourly()
            ->withoutOverlapping();
    }
}
