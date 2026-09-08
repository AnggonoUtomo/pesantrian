<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\UpsertStudentDisciplineCategoryData;

interface StudentDisciplineCategoryMutationRepository
{
    public function findActiveCategory(string $id): ?StudentDisciplineCategoryData;

    public function createCategory(UpsertStudentDisciplineCategoryData $data, ?string $actorId): StudentDisciplineCategoryData;

    /** @param array<string, int|string|null> $changes */
    public function updateCategory(string $id, array $changes): ?StudentDisciplineCategoryData;

    public function archiveCategory(string $id): ?StudentDisciplineCategoryData;
}
