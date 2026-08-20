<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Runtime;

use App\Modules\System\AuditLog\Application\Contracts\AuditRuntimeSettings;
use App\Modules\System\AuditLog\Application\DTO\AuditPaginationSettings;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;

final readonly class SystemSettingAuditRuntimeSettings implements AuditRuntimeSettings
{
    public function __construct(
        private SystemSettingReader $settings,
        private AuditPaginationSettings $paginationFallback,
    ) {}

    public function pagination(): AuditPaginationSettings
    {
        $fallback = $this->paginationFallback;
        $values = $this->settings->many([
            'pagination.per_page_options',
            'pagination.default_per_page',
        ]);
        $options = $this->positiveIntegerList($values['pagination.per_page_options']->value);
        $default = $values['pagination.default_per_page']->value;

        if ($options === null) {
            return $fallback;
        }

        return new AuditPaginationSettings(
            perPageOptions: $options,
            defaultPerPage: is_int($default) && in_array($default, $options, true)
                ? $default
                : $fallback->defaultPerPage,
        );
    }

    /** @return list<int>|null */
    private function positiveIntegerList(mixed $value): ?array
    {
        if (! is_array($value) || $value === [] || ! array_is_list($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $item) {
            if (! is_int($item) || $item <= 0) {
                return null;
            }

            $items[] = $item;
        }

        return $items;
    }
}
