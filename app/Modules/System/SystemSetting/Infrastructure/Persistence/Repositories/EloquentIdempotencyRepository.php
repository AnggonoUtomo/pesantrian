<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories;

use App\Modules\System\SystemSetting\Application\Contracts\IdempotencyRepository;
use App\Modules\System\SystemSetting\Application\DTO\IdempotencyReservationData;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\IdempotencyKeyRecord;
use DateTimeImmutable;
use Illuminate\Database\QueryException;

final class EloquentIdempotencyRepository implements IdempotencyRepository
{
    public function reserve(
        string $actorId,
        string $key,
        string $endpoint,
        string $payloadHash,
        DateTimeImmutable $expiresAt,
    ): IdempotencyReservationData {
        try {
            $record = IdempotencyKeyRecord::query()->firstOrCreate(
                [
                    'actor_id' => $actorId,
                    'endpoint' => $endpoint,
                    'key' => $key,
                ],
                [
                    'payload_hash' => $payloadHash,
                    'response_status' => 102,
                    'response_body' => ['pending' => true],
                    'expires_at' => $expiresAt,
                ],
            );
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan idempotency tidak tersedia.', previous: $exception);
        }

        return new IdempotencyReservationData(
            id: $record->id,
            payloadHash: $record->payload_hash,
            responseStatus: $record->response_status,
            responseBody: $record->response_body,
            expiresAt: DateTimeImmutable::createFromInterface($record->expires_at),
            created: $record->wasRecentlyCreated,
        );
    }

    public function complete(string $reservationId, int $responseStatus, array $responseBody): void
    {
        try {
            IdempotencyKeyRecord::query()->whereKey($reservationId)->update([
                'response_status' => $responseStatus,
                'response_body' => $responseBody,
            ]);
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan idempotency tidak tersedia.', previous: $exception);
        }
    }

    public function delete(string $reservationId): void
    {
        try {
            IdempotencyKeyRecord::query()->whereKey($reservationId)->delete();
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan idempotency tidak tersedia.', previous: $exception);
        }
    }

    public function pruneExpired(): int
    {
        try {
            return IdempotencyKeyRecord::query()
                ->where('expires_at', '<', now())
                ->delete();
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan idempotency tidak tersedia.', previous: $exception);
        }
    }
}
