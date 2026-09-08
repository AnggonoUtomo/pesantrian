<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryListFilter;

final readonly class ListStudentDisciplineCategories
{
    public function __construct(private StudentDisciplineReadRepository $repository) {}

    /** @return list<StudentDisciplineCategoryData> */
    public function execute(StudentDisciplineCategoryListFilter $filter): array
    {
        return $this->repository->categories($filter);
    }
}
