<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Runtime;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use StarterKit\Http\Idempotency\Contracts\RuntimeApiPolicy;

final readonly class SystemSettingRuntimeApiPolicy implements RuntimeApiPolicy
{
    public function __construct(
        private SystemSettingReader $settings,
    ) {}

    public function idempotencyRetentionHours(): int
    {
        return $this->settings->integer('api.idempotency.retention_hours');
    }

    public function rateLimitPerMinute(): int
    {
        return $this->settings->integer('api.rate_limit.per_minute');
    }
}
