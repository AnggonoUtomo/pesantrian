<?php

declare(strict_types=1);

namespace StarterKit\Http\Idempotency\Runtime;

use DateTimeImmutable;
use StarterKit\Http\Idempotency\Contracts\IdempotencyRepository;
use StarterKit\Http\Idempotency\DTO\IdempotencyReservationData;
use StarterKit\Http\Idempotency\Exceptions\IdempotencyStorageUnavailable;

final readonly class UnavailableIdempotencyRepository implements IdempotencyRepository
{
    public function reserve(
        string $actorId,
        string $key,
        string $endpoint,
        string $payloadHash,
        DateTimeImmutable $expiresAt,
    ): IdempotencyReservationData {
        throw $this->unavailable();
    }

    public function complete(string $reservationId, int $responseStatus, array $responseBody): void
    {
        throw $this->unavailable();
    }

    public function delete(string $reservationId): void
    {
        throw $this->unavailable();
    }

    public function pruneExpired(): int
    {
        throw $this->unavailable();
    }

    private function unavailable(): IdempotencyStorageUnavailable
    {
        return new IdempotencyStorageUnavailable('Penyimpanan idempotency tidak tersedia.');
    }
}
