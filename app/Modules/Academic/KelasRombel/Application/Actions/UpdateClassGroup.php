<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateClassGroup
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    /** @param array<string, string|int|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?ClassGroupData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.class_group.updated',
            subjectType: 'class_group',
            mutation: fn (): ?ClassGroupData => $this->repository->updateClassGroup($id, $changes),
            subjectId: static fn (?ClassGroupData $classGroup): ?string => $classGroup?->id,
            metadata: static fn (?ClassGroupData $classGroup): array => [
                'changed_fields' => array_keys($changes),
                'result' => $classGroup instanceof ClassGroupData ? self::auditResult($classGroup) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(ClassGroupData $classGroup): array
    {
        return [
            'academic_year_id' => $classGroup->academicYear->id,
            'academic_term_id' => $classGroup->academicTerm->id,
            'unit_id' => $classGroup->unit->id,
            'curriculum_id' => $classGroup->curriculum?->id,
            'class_level_id' => $classGroup->classLevel->id,
            'code' => $classGroup->code,
            'name' => $classGroup->name,
            'capacity' => $classGroup->capacity,
            'status' => $classGroup->status,
        ];
    }
}
