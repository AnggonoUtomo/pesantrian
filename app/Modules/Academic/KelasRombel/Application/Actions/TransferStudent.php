<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PlaceStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentTransferData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelPlacementException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class TransferStudent
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
        private KelasRombelReadRepository $readRepository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $sourceClassGroupId,
        string $placementId,
        string $targetClassGroupId,
        string $joinedOn,
        string $reason,
        ?string $correlationId = null,
    ): ?StudentTransferData {
        $source = $this->readRepository->findClassGroup($sourceClassGroupId);
        $target = $this->readRepository->findClassGroup($targetClassGroupId);
        $placement = $this->repository->findPlacement($placementId);

        if (! $source instanceof ClassGroupData || ! $target instanceof ClassGroupData || ! $placement instanceof StudentPlacementData) {
            return null;
        }

        $this->assertTransferIsValid($source, $target, $placement);

        $student = $this->students->findActive($placement->studentId, $target->unit->id);
        if (! $student instanceof ActiveStudentOptionData) {
            throw new KelasRombelPlacementException(
                'Pindah rombel tidak valid.',
                ['student_id' => ['Santri harus aktif dan berada pada unit rombel tujuan.']],
            );
        }

        $data = new PlaceStudentData(
            classGroupId: $target->id,
            academicTermId: $target->academicTerm->id,
            studentId: $student->id,
            studentNo: $student->studentNo,
            joinedOn: $joinedOn,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.student.transferred',
            subjectType: 'class_group_student',
            mutation: fn (): ?StudentTransferData => $this->repository->transferStudent($placement->id, $data, $reason),
            subjectId: static fn (?StudentTransferData $transfer): ?string => $transfer?->current->id,
            metadata: static fn (?StudentTransferData $transfer): array => [
                'changed_fields' => ['class_group_id', 'joined_on', 'left_on', 'status', 'reason'],
                'result' => $transfer instanceof StudentTransferData ? [
                    'previous' => self::auditResult($transfer->previous),
                    'current' => self::auditResult($transfer->current),
                ] : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function assertTransferIsValid(ClassGroupData $source, ClassGroupData $target, StudentPlacementData $placement): void
    {
        if ($placement->classGroupId !== $source->id || $placement->status !== 'active') {
            throw new KelasRombelPlacementException(
                'Pindah rombel tidak valid.',
                ['placement' => ['Placement aktif tidak ditemukan pada rombel asal.']],
            );
        }

        if ($source->id === $target->id) {
            throw new KelasRombelPlacementException(
                'Pindah rombel tidak valid.',
                ['target_class_group_id' => ['Rombel tujuan harus berbeda dari rombel asal.']],
            );
        }

        if ($target->status !== 'active' || $target->archivedAt !== null) {
            throw new KelasRombelPlacementException(
                'Pindah rombel tidak valid.',
                ['target_class_group_id' => ['Rombel tujuan harus aktif dan belum diarsipkan.']],
            );
        }

        if ($source->academicTerm->id !== $target->academicTerm->id) {
            throw new KelasRombelPlacementException(
                'Pindah rombel tidak valid.',
                ['target_class_group_id' => ['Rombel tujuan harus berada pada semester yang sama.']],
            );
        }
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentPlacementData $placement): array
    {
        return [
            'class_group_id' => $placement->classGroupId,
            'academic_term_id' => $placement->academicTermId,
            'student_id' => $placement->studentId,
            'student_no' => $placement->studentNo,
            'joined_on' => $placement->joinedOn,
            'left_on' => $placement->leftOn,
            'status' => $placement->status,
            'reason' => $placement->reason,
        ];
    }
}
