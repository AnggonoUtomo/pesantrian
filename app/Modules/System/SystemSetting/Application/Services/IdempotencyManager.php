<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\IdempotencyRepository;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\DTO\IdempotencyDecision;
use App\Modules\System\SystemSetting\Application\DTO\IdempotencyReservationData;
use App\Modules\System\SystemSetting\Domain\Exceptions\IdempotencyConflict;
use DateTimeImmutable;

final readonly class IdempotencyManager
{
    /** @var list<string> */
    private const SENSITIVE_PATTERNS = [
        'password',
        'token',
        'secret',
        'credential',
        'authorization',
        'cookie',
        'session',
        'api_key',
    ];

    public function __construct(
        private IdempotencyRepository $repository,
        private SystemSettingReader $settings,
    ) {}

    /** @param array<string, mixed> $payload */
    public function begin(string $actorId, string $endpoint, string $key, array $payload): IdempotencyDecision
    {
        $payloadHash = hash('sha256', (string) json_encode(
            $this->canonicalize($payload),
            JSON_THROW_ON_ERROR,
        ));
        $expiresAt = new DateTimeImmutable(sprintf(
            '+%d hours',
            $this->settings->integer('api.idempotency.retention_hours'),
        ));

        $reservation = $this->repository->reserve(
            $actorId,
            $key,
            $endpoint,
            $payloadHash,
            $expiresAt,
        );

        if (! $reservation->created && $reservation->expiresAt < new DateTimeImmutable) {
            $this->repository->delete($reservation->id);
            $reservation = $this->repository->reserve(
                $actorId,
                $key,
                $endpoint,
                $payloadHash,
                $expiresAt,
            );
        }

        return $this->decision($reservation, $payloadHash);
    }

    /** @param array<string, mixed> $responseBody */
    public function complete(string $reservationId, int $responseStatus, array $responseBody): void
    {
        $sanitized = $this->sanitizeValue($responseBody, '', 1);

        $this->repository->complete(
            $reservationId,
            $responseStatus,
            is_array($sanitized) ? $sanitized : [],
        );
    }

    public function cancel(string $reservationId): void
    {
        $this->repository->delete($reservationId);
    }

    private function decision(IdempotencyReservationData $reservation, string $payloadHash): IdempotencyDecision
    {
        if ($reservation->payloadHash !== $payloadHash) {
            throw new IdempotencyConflict('Idempotency-Key sudah dipakai untuk payload berbeda.');
        }

        if ($reservation->created) {
            return IdempotencyDecision::proceed($reservation->id);
        }

        if ($reservation->responseStatus === 102) {
            throw new IdempotencyConflict('Request dengan Idempotency-Key ini masih diproses.');
        }

        return IdempotencyDecision::replay(
            $reservation->id,
            $reservation->responseStatus,
            $reservation->responseBody,
        );
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
    }

    private function sanitizeValue(mixed $value, string $key, int $depth): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if ($depth > 5) {
            return '[TRUNCATED]';
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach (array_slice($value, 0, 50, true) as $nestedKey => $nestedValue) {
                $normalizedKey = is_string($nestedKey) ? $nestedKey : (string) $nestedKey;
                $sanitized[$nestedKey] = $this->sanitizeValue($nestedValue, $normalizedKey, $depth + 1);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return mb_substr($value, 0, 500);
        }

        return is_scalar($value) || $value === null ? $value : '[FILTERED]';
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = mb_strtolower($key);

        return array_any(
            self::SENSITIVE_PATTERNS,
            static fn (string $pattern): bool => str_contains($normalized, $pattern),
        );
    }
}
