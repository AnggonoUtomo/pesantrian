<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Queries;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;

final readonly class ListAcademicTerms
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(AcademicTermListFilter $filter): PaginatedAcademicTermData
    {
        return $this->repository->paginateTerms($filter);
    }
}
