<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

final readonly class BulkUserLifecycleResult
{
    private function __construct(
        public bool $completed,
        public int $processed,
        public ?string $message,
    ) {}

    public static function completed(int $processed): self
    {
        return new self(true, $processed, null);
    }

    public static function rejected(string $message): self
    {
        return new self(false, 0, $message);
    }
}
