<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateDormitoryRoom
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    /** @param array<string, string|int> $changes */
    public function execute(?Authenticatable $actor, string $dormitoryId, string $roomId, array $changes, ?string $correlationId = null): ?DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.room.updated',
            subjectType: 'dormitory_room',
            mutation: fn (): ?DormitoryData => $this->repository->updateRoom($dormitoryId, $roomId, $changes),
            subjectId: static fn (?DormitoryData $dormitory): ?string => $dormitory?->id,
            metadata: static fn (?DormitoryData $dormitory): array => [
                'changed_fields' => array_keys($changes),
                'result' => $dormitory instanceof DormitoryData ? self::auditResult($dormitory) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(DormitoryData $dormitory): array
    {
        return [
            'dormitory_id' => $dormitory->id,
            'room_count' => $dormitory->roomCount,
            'capacity' => $dormitory->capacity,
        ];
    }
}
