<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Queries;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\PaginatedUserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;

final readonly class ListUsers
{
    public function __construct(private UserRepository $repository) {}

    public function execute(UserListFilter $filter): PaginatedUserData
    {
        return $this->repository->paginate($filter);
    }
}
