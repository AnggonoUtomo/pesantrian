<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentDisciplineCategory
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineCategoryMutationRepository $repository,
    ) {}

    /** @param array<string, int|string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?StudentDisciplineCategoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kedisiplinan_santri.category.updated',
            subjectType: 'student_discipline_category',
            mutation: fn (): ?StudentDisciplineCategoryData => $this->repository->updateCategory($id, $changes),
            subjectId: static fn (?StudentDisciplineCategoryData $category): ?string => $category?->id,
            metadata: static fn (?StudentDisciplineCategoryData $category): array => [
                'changed_fields' => array_keys($changes),
                'result' => $category instanceof StudentDisciplineCategoryData ? self::auditCategory($category) : null,
            ],
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
