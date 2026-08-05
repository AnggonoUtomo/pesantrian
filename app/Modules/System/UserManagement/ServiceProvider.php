<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement;

use App\Models\User;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Modules\System\UserManagement\Presentation\Policies\UserManagementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        Gate::policy(User::class, UserManagementPolicy::class);
    }
}
