<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class RuntimeSettingData
{
    public function __construct(
        public string $appName,
        public ?string $logoPath,
        public string $faviconPath,
        public string $paletteDefault,
        public string $typographyDefault,
        public string $appearanceDefault,
        public bool $monitoringExternalRequested,
        public bool $monitoringExternalAvailable,
        public bool $monitoringExternalEnabled,
        public int $rtoHours,
        public int $rpoHours,
        public int $sessionIdleMinutes,
        public int $sessionAbsoluteHours,
    ) {}

    /** @return array<string, int|bool|string|null> */
    public function branding(): array
    {
        return [
            'appName' => $this->appName,
            'logoPath' => $this->logoPath,
            'faviconPath' => $this->faviconPath,
            'paletteDefault' => $this->paletteDefault,
            'typographyDefault' => $this->typographyDefault,
            'appearanceDefault' => $this->appearanceDefault,
        ];
    }

    /** @return array<string, int|bool|string|null> */
    public function runtime(): array
    {
        return [
            'monitoringExternalRequested' => $this->monitoringExternalRequested,
            'monitoringExternalAvailable' => $this->monitoringExternalAvailable,
            'monitoringExternalEnabled' => $this->monitoringExternalEnabled,
            'rtoHours' => $this->rtoHours,
            'rpoHours' => $this->rpoHours,
            'sessionIdleMinutes' => $this->sessionIdleMinutes,
            'sessionAbsoluteHours' => $this->sessionAbsoluteHours,
        ];
    }

    /** @return array<string, int|bool|string|null> */
    public function diagnostic(): array
    {
        return [
            'monitoring_external_requested' => $this->monitoringExternalRequested,
            'monitoring_external_available' => $this->monitoringExternalAvailable,
            'monitoring_external_enabled' => $this->monitoringExternalEnabled,
            'rto_hours' => $this->rtoHours,
            'rpo_hours' => $this->rpoHours,
            'session_idle_minutes' => $this->sessionIdleMinutes,
            'session_absolute_hours' => $this->sessionAbsoluteHours,
        ];
    }
}
