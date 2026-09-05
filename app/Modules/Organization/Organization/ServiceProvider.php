<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization;

use App\Modules\Organization\Organization\Application\Contracts\DormitoryUnitReader;
use App\Modules\Organization\Organization\Application\Contracts\EducationUnitReader;
use App\Modules\Organization\Organization\Application\Contracts\OrganizationActivityPublisher;
use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Infrastructure\Events\LaravelOrganizationActivityPublisher;
use App\Modules\Organization\Organization\Infrastructure\Readers\EloquentDormitoryUnitReader;
use App\Modules\Organization\Organization\Infrastructure\Readers\EloquentEducationUnitReader;
use App\Modules\Organization\Organization\Infrastructure\Repositories\EloquentOrganizationUnitRepository;
use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrganizationActivityPublisher::class, LaravelOrganizationActivityPublisher::class);
        $this->app->bind(OrganizationUnitRepository::class, EloquentOrganizationUnitRepository::class);
        $this->app->bind(EducationUnitReader::class, EloquentEducationUnitReader::class);
        $this->app->bind(DormitoryUnitReader::class, EloquentDormitoryUnitReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}
