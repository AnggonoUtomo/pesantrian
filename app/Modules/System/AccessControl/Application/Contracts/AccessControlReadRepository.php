<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use App\Modules\System\AccessControl\Application\DTO\AccessControlDashboardData;
use App\Modules\System\AccessControl\Application\DTO\PaginatedPermissionData;
use App\Modules\System\AccessControl\Application\DTO\PaginatedRoleData;
use App\Modules\System\AccessControl\Application\DTO\PermissionListFilter;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\DTO\RoleListFilter;

interface AccessControlReadRepository
{
    public function dashboard(): AccessControlDashboardData;

    public function paginateRoles(RoleListFilter $filter): PaginatedRoleData;

    public function findRole(string $roleId): ?RoleData;

    public function paginatePermissions(PermissionListFilter $filter): PaginatedPermissionData;
}
