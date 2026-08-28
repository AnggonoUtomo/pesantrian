<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Queries;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;

final readonly class ListAcademicYears
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(AcademicYearListFilter $filter): PaginatedAcademicYearData
    {
        return $this->repository->paginateYears($filter);
    }
}
