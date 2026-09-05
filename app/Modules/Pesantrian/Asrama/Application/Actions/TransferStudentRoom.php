<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PlaceStudentRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomTransferData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaPlacementException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class TransferStudentRoom
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $dormitoryId,
        string $placementId,
        string $targetRoomId,
        string $startedAt,
        string $reason,
        ?string $correlationId = null,
    ): ?StudentRoomTransferData {
        $placement = $this->repository->findPlacement($placementId);
        $targetRoom = $this->repository->findRoomForPlacement($targetRoomId);

        if (! $placement instanceof StudentRoomPlacementData || ! $targetRoom instanceof DormitoryRoomPlacementContextData || $targetRoom->dormitoryId !== $dormitoryId) {
            return null;
        }

        $this->assertSourceIsValid($placement, $dormitoryId, $targetRoomId);
        $student = $this->students->findActive($placement->studentId, $targetRoom->dormitoryUnitId);
        $this->assertTargetIsValid($targetRoom, $student);

        $data = new PlaceStudentRoomData(
            studentId: $student->id,
            dormitoryRoomId: $targetRoom->roomId,
            studentNo: $student->studentNo,
            startedAt: $startedAt,
            createdBy: $actor ? (string) $actor->getAuthIdentifier() : null,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.student.transferred',
            subjectType: 'student_room_placement',
            mutation: fn (): ?StudentRoomTransferData => $this->repository->transferStudent($placement->id, $data, $reason),
            subjectId: static fn (?StudentRoomTransferData $transfer): ?string => $transfer?->current->id,
            metadata: static fn (?StudentRoomTransferData $transfer): array => [
                'changed_fields' => ['dormitory_room_id', 'started_at', 'ended_at', 'status', 'reason'],
                'result' => $transfer instanceof StudentRoomTransferData ? [
                    'previous' => self::auditResult($transfer->previous),
                    'current' => self::auditResult($transfer->current),
                ] : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function assertSourceIsValid(StudentRoomPlacementData $placement, string $dormitoryId, string $targetRoomId): void
    {
        if ($placement->status !== 'active') {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['placement' => ['Penempatan aktif tidak ditemukan pada asrama.']],
            );
        }

        if ($placement->dormitoryRoomId === $targetRoomId) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['target_room_id' => ['Kamar tujuan harus berbeda dari kamar asal.']],
            );
        }

        $sourceRoom = $this->repository->findRoomForPlacement($placement->dormitoryRoomId);
        if (! $sourceRoom instanceof DormitoryRoomPlacementContextData || $sourceRoom->dormitoryId !== $dormitoryId) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['placement' => ['Penempatan aktif tidak ditemukan pada asrama.']],
            );
        }
    }

    private function assertTargetIsValid(DormitoryRoomPlacementContextData $room, ?ActiveStudentOptionData $student): void
    {
        if (! $student instanceof ActiveStudentOptionData) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['student_id' => ['Santri harus aktif dan berada pada unit asrama tujuan.']],
            );
        }

        if ($room->dormitoryStatus !== 'active' || $room->dormitoryArchivedAt !== null || $room->roomStatus !== 'active' || $room->roomArchivedAt !== null) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['target_room_id' => ['Kamar tujuan harus aktif dan belum diarsipkan.']],
            );
        }

        if ($room->availableCapacity() < 1) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['target_room_id' => ['Kamar tujuan sudah penuh.']],
            );
        }

        if (! in_array($room->genderPolicy, ['mixed', 'unspecified'], true) && $student->gender !== null && $student->gender !== $room->genderPolicy) {
            throw new AsramaPlacementException(
                'Pindah kamar asrama tidak valid.',
                ['student_id' => ['Jenis kelamin santri tidak sesuai dengan kebijakan asrama tujuan.']],
            );
        }
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
