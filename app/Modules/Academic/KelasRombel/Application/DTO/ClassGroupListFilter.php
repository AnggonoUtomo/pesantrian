<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ClassGroupListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $academicYearId,
        public ?string $academicTermId,
        public ?string $unitId,
        public ?string $curriculumId,
        public ?string $status,
        public string $archived,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
