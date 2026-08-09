<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

final readonly class PaginatedUserData
{
    /** @param list<UserData> $data */
    public function __construct(
        public array $data,
        public int $total,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
    ) {}
}
