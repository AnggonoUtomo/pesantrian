<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Queries;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\DTO\PaginatedPermissionData;
use App\Modules\System\AccessControl\Application\DTO\PermissionListFilter;

final readonly class ListPermissions
{
    public function __construct(private AccessControlReadRepository $repository) {}

    public function execute(PermissionListFilter $filter): PaginatedPermissionData
    {
        return $this->repository->paginatePermissions($filter);
    }
}
