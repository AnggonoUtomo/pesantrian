<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaPlacementException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RemoveStudentRoomPlacement
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $dormitoryId,
        string $placementId,
        string $endedAt,
        string $reason,
        ?string $correlationId = null,
    ): ?StudentRoomPlacementData {
        $placement = $this->repository->findPlacement($placementId);

        if (! $placement instanceof StudentRoomPlacementData) {
            return null;
        }

        $room = $this->repository->findRoomForPlacement($placement->dormitoryRoomId);
        if ($placement->status !== 'active' || ! $room instanceof DormitoryRoomPlacementContextData || $room->dormitoryId !== $dormitoryId) {
            throw new AsramaPlacementException(
                'Keluar kamar asrama tidak valid.',
                ['placement' => ['Penempatan aktif tidak ditemukan pada asrama.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'asrama.student.removed',
            subjectType: 'student_room_placement',
            mutation: fn (): ?StudentRoomPlacementData => $this->repository->removeStudent($placement->id, $endedAt, $reason, $actorId),
            subjectId: static fn (?StudentRoomPlacementData $removed): ?string => $removed?->id,
            metadata: static fn (?StudentRoomPlacementData $removed): array => [
                'changed_fields' => ['ended_at', 'status', 'reason'],
                'result' => $removed instanceof StudentRoomPlacementData ? self::auditResult($removed) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentRoomPlacementData $placement): array
    {
        return [
            'student_id' => $placement->studentId,
            'dormitory_room_id' => $placement->dormitoryRoomId,
            'student_no' => $placement->studentNo,
            'started_at' => $placement->startedAt,
            'ended_at' => $placement->endedAt,
            'status' => $placement->status,
            'reason' => $placement->reason,
        ];
    }
}
