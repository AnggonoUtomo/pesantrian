<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\UpsertStudentDisciplineCategoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentDisciplineCategory
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineCategoryMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertStudentDisciplineCategoryData $data, ?string $correlationId = null): StudentDisciplineCategoryData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.category.created',
            subjectType: 'student_discipline_category',
            mutation: fn (): StudentDisciplineCategoryData => $this->repository->createCategory($data, $actorId),
            subjectId: static fn (StudentDisciplineCategoryData $category): string => $category->id,
            metadata: static fn (StudentDisciplineCategoryData $category): array => [
                'changed_fields' => ['code', 'name', 'description', 'default_severity', 'default_points', 'status'],
                'result' => self::auditCategory($category),
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
