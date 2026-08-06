<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class IdempotencyDecision
{
    /** @param array<string, mixed>|null $responseBody */
    private function __construct(
        public string $reservationId,
        public ?int $responseStatus,
        public ?array $responseBody,
    ) {}

    public static function proceed(string $reservationId): self
    {
        return new self($reservationId, null, null);
    }

    /** @param array<string, mixed> $responseBody */
    public static function replay(string $reservationId, int $responseStatus, array $responseBody): self
    {
        return new self($reservationId, $responseStatus, $responseBody);
    }

    public function isReplay(): bool
    {
        return $this->responseStatus !== null && $this->responseBody !== null;
    }
}
