<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use App\Modules\System\AccessControl\Application\DTO\RoleOption;

interface RoleCatalogCapability
{
    /** @return list<RoleOption> */
    public function listRoles(): array;

    public function findRole(string $roleId): ?RoleOption;
}
