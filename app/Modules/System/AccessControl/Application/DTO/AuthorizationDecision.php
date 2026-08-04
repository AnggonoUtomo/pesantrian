<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class AuthorizationDecision
{
    private function __construct(
        public bool $allowed,
        public string $reason,
    ) {}

    public static function allow(string $reason = 'allowed'): self
    {
        return new self(true, $reason);
    }

    public static function deny(string $reason = 'denied'): self
    {
        return new self(false, $reason);
    }
}
