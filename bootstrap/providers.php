<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use StarterKit\Modules\ModuleRuntimeServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ModuleRuntimeServiceProvider::class,
];
