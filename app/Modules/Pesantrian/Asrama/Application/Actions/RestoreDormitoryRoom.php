<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RestoreDormitoryRoom
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $dormitoryId, string $roomId, ?string $correlationId = null): ?DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.room.restored',
            subjectType: 'dormitory_room',
            mutation: fn (): ?DormitoryData => $this->repository->restoreRoom($dormitoryId, $roomId),
            subjectId: static fn (?DormitoryData $dormitory): ?string => $dormitory?->id,
            metadata: static fn (?DormitoryData $dormitory): array => [
                'changed_fields' => ['rooms.archived_at', 'rooms.archived_by'],
                'result' => $dormitory instanceof DormitoryData ? [
                    'dormitory_id' => $dormitory->id,
                    'room_id' => $roomId,
                ] : null,
            ],
            correlationId: $correlationId,
        );
    }
}
