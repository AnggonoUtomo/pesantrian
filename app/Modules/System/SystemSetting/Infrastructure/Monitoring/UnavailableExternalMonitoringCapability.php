<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Monitoring;

use App\Modules\System\SystemSetting\Application\Contracts\ExternalMonitoringCapability;

final readonly class UnavailableExternalMonitoringCapability implements ExternalMonitoringCapability
{
    public function available(): bool
    {
        return false;
    }
}
