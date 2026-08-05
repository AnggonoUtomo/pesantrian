<?php

use App\Modules\System\AccessControl\ServiceProvider as AccessControlServiceProvider;
use App\Modules\System\UserManagement\ServiceProvider as UserManagementServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AccessControlServiceProvider::class,
    UserManagementServiceProvider::class,
];
