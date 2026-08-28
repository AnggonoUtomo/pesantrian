<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class PaginatedAcademicYearData
{
    /** @param list<AcademicYearData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
