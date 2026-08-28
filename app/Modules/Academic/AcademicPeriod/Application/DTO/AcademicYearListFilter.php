<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class AcademicYearListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
