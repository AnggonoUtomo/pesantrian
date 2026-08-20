<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Infrastructure\Runtime;

use App\Modules\System\AuditLog\Application\Contracts\AuditRuntimeSettings;
use App\Modules\System\AuditLog\Application\DTO\AuditPaginationSettings;
use Illuminate\Contracts\Config\Repository;

final readonly class DefaultAuditRuntimeSettings implements AuditRuntimeSettings
{
    public function __construct(private Repository $config) {}

    public function pagination(): AuditPaginationSettings
    {
        $configuredOptions = $this->config->get('audit-log.pagination.per_page_options');
        $options = is_array($configuredOptions)
            ? array_values(array_unique(array_filter(
                $configuredOptions,
                static fn (mixed $item): bool => is_int($item) && $item > 0,
            )))
            : [];
        $options = $options === [] ? [5, 10, 25, 50, 100] : $options;
        $configuredDefault = $this->config->get('audit-log.pagination.default_per_page');
        $default = is_int($configuredDefault) && $configuredDefault > 0 ? $configuredDefault : 25;

        return new AuditPaginationSettings(
            perPageOptions: $options,
            defaultPerPage: in_array($default, $options, true) ? $default : $options[0],
        );
    }
}
