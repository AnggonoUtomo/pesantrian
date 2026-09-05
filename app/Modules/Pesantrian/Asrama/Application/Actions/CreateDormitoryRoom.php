<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryRoomData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateDormitoryRoom
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertDormitoryRoomData $data, ?string $correlationId = null): DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.room.created',
            subjectType: 'dormitory_room',
            mutation: fn (): DormitoryData => $this->repository->createRoom($data),
            subjectId: static fn (DormitoryData $dormitory): string => $dormitory->id,
            metadata: static fn (DormitoryData $dormitory): array => [
                'changed_fields' => ['dormitory_id', 'code', 'name', 'capacity', 'status'],
                'result' => self::auditResult($dormitory),
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
