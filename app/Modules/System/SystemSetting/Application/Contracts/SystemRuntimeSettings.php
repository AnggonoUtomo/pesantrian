<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

use App\Modules\System\SystemSetting\Application\DTO\RuntimeSettingData;

interface SystemRuntimeSettings
{
    public function current(): RuntimeSettingData;
}
