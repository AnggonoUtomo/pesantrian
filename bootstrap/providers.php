<?php

use App\Modules\System\AccessControl\ServiceProvider as AccessControlServiceProvider;
use App\Modules\System\AuditLog\ServiceProvider as AuditLogServiceProvider;
use App\Modules\System\SystemSetting\ServiceProvider as SystemSettingServiceProvider;
use App\Modules\System\UserManagement\ServiceProvider as UserManagementServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AccessControlServiceProvider::class,
    UserManagementServiceProvider::class,
    AuditLogServiceProvider::class,
    SystemSettingServiceProvider::class,
];
