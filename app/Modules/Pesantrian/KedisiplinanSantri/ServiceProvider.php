<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Events\LaravelStudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Repositories\EloquentStudentDisciplineReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentDisciplineActivityPublisher::class, LaravelStudentDisciplineActivityPublisher::class);
        $this->app->bind(StudentDisciplineCategoryMutationRepository::class, EloquentStudentDisciplineReadRepository::class);
        $this->app->bind(StudentDisciplineReadRepository::class, EloquentStudentDisciplineReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
