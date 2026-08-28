<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Repositories\EloquentAcademicPeriodRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AcademicPeriodRepository::class, EloquentAcademicPeriodRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
