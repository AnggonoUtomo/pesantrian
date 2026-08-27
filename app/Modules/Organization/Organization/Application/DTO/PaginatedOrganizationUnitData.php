<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class PaginatedOrganizationUnitData
{
    /** @param list<OrganizationUnitData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
