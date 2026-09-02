<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class PaginatedClassGroupData
{
    /** @param list<ClassGroupData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
