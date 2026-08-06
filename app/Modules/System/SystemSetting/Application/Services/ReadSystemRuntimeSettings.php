<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\ExternalMonitoringCapability;
use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\DTO\RuntimeSettingData;

final class ReadSystemRuntimeSettings implements SystemRuntimeSettings
{
    private ?RuntimeSettingData $current = null;

    public function __construct(
        private readonly SystemSettingReader $settings,
        private readonly ExternalMonitoringCapability $monitoring,
    ) {}

    public function current(): RuntimeSettingData
    {
        if ($this->current instanceof RuntimeSettingData) {
            return $this->current;
        }

        $values = $this->settings->many([
            'branding.app_name',
            'branding.logo_path',
            'branding.favicon_path',
            'branding.palette_default',
            'branding.typography_default',
            'branding.appearance_default',
            'monitoring.external_enabled',
            'operations.rto_hours',
            'operations.rpo_hours',
            'security.session.idle_minutes',
            'security.session.absolute_hours',
        ]);

        $monitoringRequested = (bool) $values['monitoring.external_enabled']->value;
        $monitoringAvailable = $this->monitoring->available();

        return $this->current = new RuntimeSettingData(
            appName: (string) $values['branding.app_name']->value,
            logoPath: $values['branding.logo_path']->value === null ? null : (string) $values['branding.logo_path']->value,
            faviconPath: (string) $values['branding.favicon_path']->value,
            paletteDefault: (string) $values['branding.palette_default']->value,
            typographyDefault: (string) $values['branding.typography_default']->value,
            appearanceDefault: (string) $values['branding.appearance_default']->value,
            monitoringExternalRequested: $monitoringRequested,
            monitoringExternalAvailable: $monitoringAvailable,
            monitoringExternalEnabled: $monitoringRequested && $monitoringAvailable,
            rtoHours: (int) $values['operations.rto_hours']->value,
            rpoHours: (int) $values['operations.rpo_hours']->value,
            sessionIdleMinutes: (int) $values['security.session.idle_minutes']->value,
            sessionAbsoluteHours: (int) $values['security.session.absolute_hours']->value,
        );
    }
}
