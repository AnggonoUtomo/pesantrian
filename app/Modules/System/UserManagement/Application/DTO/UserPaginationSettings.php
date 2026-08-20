<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

final readonly class UserPaginationSettings
{
    /** @param list<int> $perPageOptions */
    public function __construct(
        public array $perPageOptions,
        public int $defaultPerPage,
    ) {}
}
