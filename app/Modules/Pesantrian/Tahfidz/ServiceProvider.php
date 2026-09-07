<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Repositories\EloquentTahfidzReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TahfidzReadRepository::class, EloquentTahfidzReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
