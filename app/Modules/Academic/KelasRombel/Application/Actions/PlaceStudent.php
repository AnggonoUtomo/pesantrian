<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PlaceStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelPlacementException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class PlaceStudent
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
        private KelasRombelReadRepository $readRepository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(?Authenticatable $actor, string $classGroupId, string $studentId, string $joinedOn, ?string $correlationId = null): ?StudentPlacementData
    {
        $classGroup = $this->readRepository->findClassGroup($classGroupId);

        if (! $classGroup instanceof ClassGroupData) {
            return null;
        }

        $this->assertClassGroupAcceptsPlacement($classGroup);

        $student = $this->students->findActive($studentId, $classGroup->unit->id);
        if (! $student instanceof ActiveStudentOptionData) {
            throw new KelasRombelPlacementException(
                'Penempatan santri tidak valid.',
                ['student_id' => ['Santri harus aktif dan berada pada unit rombel.']],
            );
        }

        if ($this->repository->findActivePlacementForStudentInTerm($student->id, $classGroup->academicTerm->id) instanceof StudentPlacementData) {
            throw new KelasRombelPlacementException(
                'Penempatan santri tidak valid.',
                ['student_id' => ['Santri sudah memiliki rombel aktif pada semester ini.']],
            );
        }

        $data = new PlaceStudentData(
            classGroupId: $classGroup->id,
            academicTermId: $classGroup->academicTerm->id,
            studentId: $student->id,
            studentNo: $student->studentNo,
            joinedOn: $joinedOn,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.student.placed',
            subjectType: 'class_group_student',
            mutation: fn (): StudentPlacementData => $this->repository->placeStudent($data),
            subjectId: static fn (StudentPlacementData $placement): string => $placement->id,
            metadata: static fn (StudentPlacementData $placement): array => [
                'changed_fields' => ['class_group_id', 'academic_term_id', 'student_id', 'student_no', 'joined_on', 'status'],
                'result' => self::auditResult($placement),
            ],
            correlationId: $correlationId,
        );
    }

    private function assertClassGroupAcceptsPlacement(ClassGroupData $classGroup): void
    {
        if ($classGroup->status !== 'active' || $classGroup->archivedAt !== null) {
            throw new KelasRombelPlacementException(
                'Penempatan santri tidak valid.',
                ['class_group_id' => ['Rombel harus aktif dan belum diarsipkan.']],
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
