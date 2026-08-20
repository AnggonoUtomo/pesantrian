<?php

declare(strict_types=1);

namespace StarterKit\Http\Idempotency\Runtime;

use StarterKit\Http\Idempotency\Contracts\RuntimeApiPolicy;

final readonly class DefaultRuntimeApiPolicy implements RuntimeApiPolicy
{
    public function idempotencyRetentionHours(): int
    {
        return 24;
    }

    public function rateLimitPerMinute(): int
    {
        return 60;
    }
}
