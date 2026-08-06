<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

use DateTimeImmutable;

final readonly class IdempotencyReservationData
{
    /** @param array<string, mixed> $responseBody */
    public function __construct(
        public string $id,
        public string $payloadHash,
        public int $responseStatus,
        public array $responseBody,
        public DateTimeImmutable $expiresAt,
        public bool $created,
    ) {}
}
