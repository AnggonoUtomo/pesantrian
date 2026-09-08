<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ArchiveStudentDisciplineCategory
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineCategoryMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $reason, ?string $correlationId = null): ?StudentDisciplineCategoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kedisiplinan_santri.category.archived',
            subjectType: 'student_discipline_category',
            mutation: fn (): ?StudentDisciplineCategoryData => $this->repository->archiveCategory($id),
            subjectId: static fn (?StudentDisciplineCategoryData $category): ?string => $category?->id,
            metadata: static fn (?StudentDisciplineCategoryData $category): array => [
                'changed_fields' => ['status'],
                'result' => $category instanceof StudentDisciplineCategoryData ? self::auditCategory($category) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditCategory(StudentDisciplineCategoryData $category): array
    {
        return [
            'code' => $category->code,
            'name' => $category->name,
            'default_severity' => $category->defaultSeverity,
            'default_points' => $category->defaultPoints,
            'status' => $category->status,
        ];
    }
}
