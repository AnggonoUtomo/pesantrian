<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Queries;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementListFilter;

final readonly class ListStudentAchievements
{
    public function __construct(private StudentAchievementReadRepository $repository) {}

    public function execute(StudentAchievementListFilter $filter): PaginatedStudentAchievementData
    {
        return $this->repository->paginate($filter);
    }
}
