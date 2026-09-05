<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaReadRepository;
use App\Modules\Pesantrian\Asrama\Infrastructure\Repositories\EloquentAsramaReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AsramaReadRepository::class, EloquentAsramaReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
