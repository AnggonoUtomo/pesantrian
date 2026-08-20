<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\Contracts\PermissionCatalog;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Infrastructure\Events\LaravelAccessControlActivityPublisher;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories\EloquentAccessControlReadRepository;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories\EloquentPermissionCatalog;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use App\Modules\System\AccessControl\Infrastructure\Services\SpatieAuthorizationAdapter;
use App\Modules\System\AccessControl\Infrastructure\Services\SpatieRoleAssignmentAdapter;
use App\Modules\System\AccessControl\Infrastructure\Services\SpatieRoleCatalogAdapter;
use App\Modules\System\AccessControl\Presentation\Console\Commands\SeedAccessControlCommand;
use App\Modules\System\AccessControl\Presentation\Policies\AccessControlPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthorizationCapability::class, SpatieAuthorizationAdapter::class);
        $this->app->bind(AccessControlActivityPublisher::class, LaravelAccessControlActivityPublisher::class);
        $this->app->singleton(RoleAssignmentCapability::class, SpatieRoleAssignmentAdapter::class);
        $this->app->singleton(RoleCatalogCapability::class, SpatieRoleCatalogAdapter::class);
        $this->app->bind(RoleRepository::class, EloquentRoleRepository::class);
        $this->app->bind(PermissionCatalog::class, EloquentPermissionCatalog::class);
        $this->app->bind(AccessControlReadRepository::class, EloquentAccessControlReadRepository::class);
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
