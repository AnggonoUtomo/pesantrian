<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Infrastructure\Events\LaravelKelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Infrastructure\Repositories\EloquentKelasRombelReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KelasRombelActivityPublisher::class, LaravelKelasRombelActivityPublisher::class);
        $this->app->bind(KelasRombelMutationRepository::class, EloquentKelasRombelReadRepository::class);
        $this->app->bind(KelasRombelReadRepository::class, EloquentKelasRombelReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
