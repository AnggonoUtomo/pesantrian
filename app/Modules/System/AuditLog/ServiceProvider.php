<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog;

use App\Modules\System\AccessControl\Application\Events\SystemActivityOccurred;
use App\Modules\System\AuditLog\Application\Actions\RecordAuditEntry;
use App\Modules\System\AuditLog\Application\Contracts\AuditLogRepository;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\Contracts\AuditRuntimeSettings;
use App\Modules\System\AuditLog\Application\DTO\AuditPaginationSettings;
use App\Modules\System\AuditLog\Application\Listeners\RecordAuthenticationActivity;
use App\Modules\System\AuditLog\Application\Listeners\RecordSystemActivity;
use App\Modules\System\AuditLog\Application\Services\MetadataRedactor;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Repositories\EloquentAuditLogRepository;
use App\Modules\System\AuditLog\Infrastructure\Runtime\DefaultAuditRuntimeSettings;
use App\Modules\System\AuditLog\Presentation\Policies\AuditLogPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/module.php', 'audit-log');

        $this->app->bind(AuditLogRepository::class, EloquentAuditLogRepository::class);
        $this->app->bind(AuditRecorder::class, RecordAuditEntry::class);
        $this->app->scoped(AuditRuntimeSettings::class, DefaultAuditRuntimeSettings::class);
        $this->app->scoped(
            AuditPaginationSettings::class,
            static fn (Application $app): AuditPaginationSettings => $app->make(DefaultAuditRuntimeSettings::class)->pagination(),
        );
        $this->app->singleton(MetadataRedactor::class, function (Application $app): MetadataRedactor {
            $repository = $app->make(ConfigRepository::class);

            /** @var array<string, mixed> $config */
            $config = $repository->get('audit-log.metadata', []);

            return new MetadataRedactor(
                allowedKeys: $this->stringList($config['allowed_keys'] ?? MetadataRedactor::DEFAULT_ALLOWED_KEYS),
                sensitivePatterns: $this->stringList($config['sensitive_patterns'] ?? MetadataRedactor::DEFAULT_SENSITIVE_PATTERNS),
                maxDepth: (int) ($config['max_depth'] ?? 4),
                maxItems: (int) ($config['max_items'] ?? 50),
                maxStringLength: (int) ($config['max_string_length'] ?? 500),
                maxReasonLength: (int) ($config['max_reason_length'] ?? 500),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        Gate::policy(AuditRecord::class, AuditLogPolicy::class);
        Event::listen(Login::class, [RecordAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [RecordAuthenticationActivity::class, 'handleLogout']);
        Event::listen(PasswordReset::class, [RecordAuthenticationActivity::class, 'handlePasswordReset']);
        Event::listen(Verified::class, [RecordAuthenticationActivity::class, 'handleVerified']);
        Event::listen(SystemActivityOccurred::class, RecordSystemActivity::class);
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }
}
