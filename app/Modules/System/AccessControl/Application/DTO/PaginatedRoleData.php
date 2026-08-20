<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class PaginatedRoleData
{
    /** @param list<RoleData> $data */
    public function __construct(
        public array $data,
        public int $total,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
    ) {}
}
