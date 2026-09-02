<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Infrastructure\Repositories\EloquentKelasRombelReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KelasRombelReadRepository::class, EloquentKelasRombelReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
