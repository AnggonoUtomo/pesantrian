<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseListFilter;

final readonly class ListStudentDisciplineCases
{
    public function __construct(private StudentDisciplineReadRepository $repository) {}

    public function execute(StudentDisciplineCaseListFilter $filter): PaginatedStudentDisciplineCaseData
    {
        return $this->repository->paginateCases($filter);
    }
}
