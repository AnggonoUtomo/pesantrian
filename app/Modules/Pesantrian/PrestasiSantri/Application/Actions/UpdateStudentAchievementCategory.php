<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementCategoryMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentAchievementCategory
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementCategoryMutationRepository $repository,
    ) {}

    /** @param array<string, string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?StudentAchievementCategoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'prestasi_santri.category.updated',
            subjectType: 'student_achievement_category',
            mutation: fn (): ?StudentAchievementCategoryData => $this->repository->updateCategory($id, $changes),
            subjectId: static fn (?StudentAchievementCategoryData $category): ?string => $category?->id,
            metadata: static fn (?StudentAchievementCategoryData $category): array => [
                'changed_fields' => array_keys($changes),
                'result' => $category instanceof StudentAchievementCategoryData ? self::auditCategory($category) : null,
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
