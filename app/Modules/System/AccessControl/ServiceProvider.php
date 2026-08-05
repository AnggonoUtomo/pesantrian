<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AccessControl\Infrastructure\Services\SpatieAuthorizationAdapter;
use App\Modules\System\AccessControl\Presentation\Console\Commands\SeedAccessControlCommand;
use App\Modules\System\AccessControl\Presentation\Policies\AccessControlPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthorizationCapability::class, SpatieAuthorizationAdapter::class);
        $this->commands([SeedAccessControlCommand::class]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        Gate::policy(Role::class, AccessControlPolicy::class);
    }
}
