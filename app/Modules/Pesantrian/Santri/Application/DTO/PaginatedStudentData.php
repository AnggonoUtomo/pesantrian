<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class PaginatedStudentData
{
    /** @param list<StudentData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
