<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

interface ExternalMonitoringCapability
{
    public function available(): bool;
}
