<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\AcceptedAdmissionReader;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionActivityPublisher;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Events\LaravelStudentAdmissionActivityPublisher;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Readers\EloquentAcceptedAdmissionReader;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Repositories\EloquentStudentAdmissionRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AcceptedAdmissionReader::class, EloquentAcceptedAdmissionReader::class);
        $this->app->bind(StudentAdmissionActivityPublisher::class, LaravelStudentAdmissionActivityPublisher::class);
        $this->app->bind(StudentAdmissionRepository::class, EloquentStudentAdmissionRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
