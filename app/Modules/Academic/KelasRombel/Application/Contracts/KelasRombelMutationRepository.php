<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Contracts;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertCurriculumData;

interface KelasRombelMutationRepository
{
    public function createCurriculum(UpsertCurriculumData $data): CurriculumData;

    /** @param array<string, string|null> $changes */
    public function updateCurriculum(string $id, array $changes): ?CurriculumData;

    public function createClassLevel(UpsertClassLevelData $data): ClassLevelData;

    /** @param array<string, string|int> $changes */
    public function updateClassLevel(string $id, array $changes): ?ClassLevelData;

    public function createClassGroup(UpsertClassGroupData $data): ClassGroupData;

    /** @param array<string, string|int|null> $changes */
    public function updateClassGroup(string $id, array $changes): ?ClassGroupData;
}
