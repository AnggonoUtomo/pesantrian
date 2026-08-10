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
            'pagination.per_page_options',
            'pagination.default_per_page',
        ]);

        $monitoringRequested = (bool) $values['monitoring.external_enabled']->value;
        $monitoringAvailable = $this->monitoring->available();

        return $this->current = new RuntimeSettingData(
            appName: $this->stringValue($values['branding.app_name']->value),
            logoPath: $this->nullableStringValue($values['branding.logo_path']->value),
            faviconPath: $this->stringValue($values['branding.favicon_path']->value),
            paletteDefault: $this->stringValue($values['branding.palette_default']->value),
            typographyDefault: $this->stringValue($values['branding.typography_default']->value),
            appearanceDefault: $this->stringValue($values['branding.appearance_default']->value),
            monitoringExternalRequested: $monitoringRequested,
            monitoringExternalAvailable: $monitoringAvailable,
            monitoringExternalEnabled: $monitoringRequested && $monitoringAvailable,
            rtoHours: (int) $values['operations.rto_hours']->value,
            rpoHours: (int) $values['operations.rpo_hours']->value,
            sessionIdleMinutes: (int) $values['security.session.idle_minutes']->value,
            sessionAbsoluteHours: (int) $values['security.session.absolute_hours']->value,
            paginationPerPageOptions: $this->integerListValue($values['pagination.per_page_options']->value),
            paginationDefaultPerPage: (int) $values['pagination.default_per_page']->value,
        );
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value)) {
            throw new \LogicException('Runtime setting string tidak valid.');
        }

        return $value;
    }

    private function nullableStringValue(mixed $value): ?string
    {
        return $value === null ? null : $this->stringValue($value);
    }

    /** @return list<int> */
    private function integerListValue(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \LogicException('Runtime setting daftar integer tidak valid.');
        }

        return array_values(array_map(
            static fn (mixed $item): int => is_int($item)
                ? $item
                : throw new \LogicException('Runtime setting daftar integer tidak valid.'),
            $value,
        ));
    }
}
