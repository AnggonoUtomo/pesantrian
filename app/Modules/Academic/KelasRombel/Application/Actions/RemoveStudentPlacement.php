<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelPlacementException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RemoveStudentPlacement
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $classGroupId,
        string $placementId,
        string $leftOn,
        string $reason,
        ?string $correlationId = null,
    ): ?StudentPlacementData {
        $placement = $this->repository->findPlacement($placementId);

        if (! $placement instanceof StudentPlacementData) {
            return null;
        }

        if ($placement->classGroupId !== $classGroupId || $placement->status !== 'active') {
            throw new KelasRombelPlacementException(
                'Keluar rombel tidak valid.',
                ['placement' => ['Placement aktif tidak ditemukan pada rombel.']],
            );
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.student.removed',
            subjectType: 'class_group_student',
            mutation: fn (): ?StudentPlacementData => $this->repository->removeStudent($placement->id, $leftOn, $reason),
            subjectId: static fn (?StudentPlacementData $removed): ?string => $removed?->id,
            metadata: static fn (?StudentPlacementData $removed): array => [
                'changed_fields' => ['left_on', 'status', 'reason'],
                'result' => $removed instanceof StudentPlacementData ? self::auditResult($removed) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
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
