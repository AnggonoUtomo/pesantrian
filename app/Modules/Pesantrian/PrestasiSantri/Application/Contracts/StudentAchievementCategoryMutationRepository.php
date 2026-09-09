<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Contracts;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\UpsertStudentAchievementCategoryData;

interface StudentAchievementCategoryMutationRepository
{
    public function findActiveCategory(string $id): ?StudentAchievementCategoryData;

    public function createCategory(UpsertStudentAchievementCategoryData $data, ?string $actorId): StudentAchievementCategoryData;

    /** @param array<string, string|null> $changes */
    public function updateCategory(string $id, array $changes): ?StudentAchievementCategoryData;

    public function archiveCategory(string $id, string $reason, ?string $actorId): ?StudentAchievementCategoryData;
}
