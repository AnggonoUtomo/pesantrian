<?php

declare(strict_types=1);

namespace StarterKit\Http\Idempotency\Contracts;

interface RuntimeApiPolicy
{
    public function idempotencyRetentionHours(): int;

    public function rateLimitPerMinute(): int;
}
