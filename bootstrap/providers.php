<?php

use App\Modules\System\AccessControl\ServiceProvider as AccessControlServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AccessControlServiceProvider::class,
];
