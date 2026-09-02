<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RestoreClassGroup
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $classGroupId, ?string $correlationId = null): ?ClassGroupData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.class_group.restored',
            subjectType: 'class_group',
            mutation: fn (): ?ClassGroupData => $this->repository->restoreClassGroup($classGroupId),
            subjectId: static fn (?ClassGroupData $classGroup): ?string => $classGroup?->id,
            metadata: static fn (?ClassGroupData $classGroup): array => [
                'changed_fields' => ['status', 'archived_at', 'archived_by'],
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
            'code' => $classGroup->code,
            'name' => $classGroup->name,
            'status' => $classGroup->status,
            'archived_at' => $classGroup->archivedAt,
        ];
    }
}
