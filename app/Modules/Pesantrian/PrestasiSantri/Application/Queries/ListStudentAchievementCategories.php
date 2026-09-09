<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Queries;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryListFilter;

final readonly class ListStudentAchievementCategories
{
    public function __construct(private StudentAchievementReadRepository $repository) {}

    /** @return list<StudentAchievementCategoryData> */
    public function execute(StudentAchievementCategoryListFilter $filter): array
    {
        return $this->repository->categories($filter);
    }
}
