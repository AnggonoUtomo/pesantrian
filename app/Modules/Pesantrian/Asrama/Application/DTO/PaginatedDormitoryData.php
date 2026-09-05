<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class PaginatedDormitoryData
{
    /** @param list<DormitoryData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
