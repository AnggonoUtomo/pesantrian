<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

use App\Modules\System\SystemSetting\Application\DTO\IdempotencyReservationData;
use DateTimeImmutable;

interface IdempotencyRepository
{
    public function reserve(
        string $actorId,
        string $key,
        string $endpoint,
        string $payloadHash,
        DateTimeImmutable $expiresAt,
    ): IdempotencyReservationData;

    /** @param array<string, mixed> $responseBody */
    public function complete(string $reservationId, int $responseStatus, array $responseBody): void;

    public function delete(string $reservationId): void;

    public function pruneExpired(): int;
}
