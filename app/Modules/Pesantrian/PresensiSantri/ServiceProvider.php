<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Events\LaravelStudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Repositories\EloquentStudentAttendanceReadRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentAttendanceReadRepository::class, EloquentStudentAttendanceReadRepository::class);
        $this->app->bind(StudentAttendanceMutationRepository::class, EloquentStudentAttendanceReadRepository::class);
        $this->app->bind(StudentAttendanceActivityPublisher::class, LaravelStudentAttendanceActivityPublisher::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
