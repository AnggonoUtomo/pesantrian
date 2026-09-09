<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Contracts;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryListFilter;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementListFilter;

interface StudentAchievementReadRepository
{
    /** @return list<StudentAchievementCategoryData> */
    public function categories(StudentAchievementCategoryListFilter $filter): array;

    public function paginate(StudentAchievementListFilter $filter): PaginatedStudentAchievementData;

    public function find(string $id): ?StudentAchievementData;
}
