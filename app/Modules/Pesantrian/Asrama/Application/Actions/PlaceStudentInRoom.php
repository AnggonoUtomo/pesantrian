<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PlaceStudentRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaPlacementException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class PlaceStudentInRoom
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $dormitoryId,
        string $studentId,
        string $roomId,
        string $startedAt,
        ?string $correlationId = null,
    ): ?StudentRoomPlacementData {
        $room = $this->repository->findRoomForPlacement($roomId);

        if (! $room instanceof DormitoryRoomPlacementContextData || $room->dormitoryId !== $dormitoryId) {
            return null;
        }

        $student = $this->students->findActive($studentId, $room->dormitoryUnitId);
        $this->assertPlacementIsValid($room, $student);

        if ($this->repository->findActivePlacementForStudent($studentId) instanceof StudentRoomPlacementData) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['student_id' => ['Santri sudah memiliki kamar asrama aktif.']],
            );
        }

        $data = new PlaceStudentRoomData(
            studentId: $student->id,
            dormitoryRoomId: $room->roomId,
            studentNo: $student->studentNo,
            startedAt: $startedAt,
            createdBy: $actor ? (string) $actor->getAuthIdentifier() : null,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.student.placed',
            subjectType: 'student_room_placement',
            mutation: fn (): StudentRoomPlacementData => $this->repository->placeStudent($data),
            subjectId: static fn (StudentRoomPlacementData $placement): string => $placement->id,
            metadata: static fn (StudentRoomPlacementData $placement): array => [
                'changed_fields' => ['student_id', 'dormitory_room_id', 'student_no', 'started_at', 'status'],
                'result' => self::auditResult($placement),
            ],
            correlationId: $correlationId,
        );
    }

    private function assertPlacementIsValid(DormitoryRoomPlacementContextData $room, ?ActiveStudentOptionData $student): void
    {
        if (! $student instanceof ActiveStudentOptionData) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['student_id' => ['Santri harus aktif dan berada pada unit asrama.']],
            );
        }

        if ($room->dormitoryStatus !== 'active' || $room->dormitoryArchivedAt !== null) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['dormitory_id' => ['Asrama harus aktif dan belum diarsipkan.']],
            );
        }

        if ($room->roomStatus !== 'active' || $room->roomArchivedAt !== null) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['dormitory_room_id' => ['Kamar asrama harus aktif dan belum diarsipkan.']],
            );
        }

        if ($room->availableCapacity() < 1) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['dormitory_room_id' => ['Kamar asrama sudah penuh.']],
            );
        }

        if (! in_array($room->genderPolicy, ['mixed', 'unspecified'], true) && $student->gender !== null && $student->gender !== $room->genderPolicy) {
            throw new AsramaPlacementException(
                'Penempatan kamar asrama tidak valid.',
                ['student_id' => ['Jenis kelamin santri tidak sesuai dengan kebijakan asrama.']],
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
