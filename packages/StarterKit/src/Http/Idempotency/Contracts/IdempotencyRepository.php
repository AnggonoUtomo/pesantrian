<?php

declare(strict_types=1);

namespace StarterKit\Http\Idempotency\Contracts;

use DateTimeImmutable;
use StarterKit\Http\Idempotency\DTO\IdempotencyReservationData;

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
