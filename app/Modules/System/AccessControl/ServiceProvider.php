<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl;

use Illuminate\Support\ServiceProvider as FrameworkServiceProvider;

final class ServiceProvider extends FrameworkServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
