<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementCategoryMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\UpsertStudentAchievementCategoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentAchievementCategory
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementCategoryMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertStudentAchievementCategoryData $data, ?string $correlationId = null): StudentAchievementCategoryData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.category.created',
            subjectType: 'student_achievement_category',
            mutation: fn (): StudentAchievementCategoryData => $this->repository->createCategory($data, $actorId),
            subjectId: static fn (StudentAchievementCategoryData $category): string => $category->id,
            metadata: static fn (StudentAchievementCategoryData $category): array => [
                'changed_fields' => ['code', 'name', 'description', 'status'],
                'result' => self::auditCategory($category),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditCategory(StudentAchievementCategoryData $category): array
    {
        return [
            'code' => $category->code,
            'name' => $category->name,
            'status' => $category->status,
        ];
    }
}
