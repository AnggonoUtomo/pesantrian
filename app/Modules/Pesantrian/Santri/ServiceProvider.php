<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Infrastructure\Events\LaravelStudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Infrastructure\Repositories\EloquentStudentRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentActivityPublisher::class, LaravelStudentActivityPublisher::class);
        $this->app->bind(StudentRepository::class, EloquentStudentRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
