<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Queries;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;

final readonly class ListUsers
{
    public function __construct(private UserRepository $repository) {}

    /** @return list<UserData> */
    public function execute(UserListFilter $filter): array
    {
        return $this->repository->list($filter);
    }
}
