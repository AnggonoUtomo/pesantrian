<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\ApprovedStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\LateStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Events\LaravelStudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Readers\EloquentApprovedStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Readers\EloquentLateStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Repositories\EloquentStudentPermitReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentPermitActivityPublisher::class, LaravelStudentPermitActivityPublisher::class);
        $this->app->bind(StudentPermitMutationRepository::class, EloquentStudentPermitReadRepository::class);
        $this->app->bind(StudentPermitReadRepository::class, EloquentStudentPermitReadRepository::class);
        $this->app->bind(ApprovedStudentPermitReader::class, EloquentApprovedStudentPermitReader::class);
        $this->app->bind(LateStudentPermitReader::class, EloquentLateStudentPermitReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
