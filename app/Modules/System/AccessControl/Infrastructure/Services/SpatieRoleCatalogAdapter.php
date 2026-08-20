<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Services;

use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\AccessControl\Application\DTO\RoleOption;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;

final class SpatieRoleCatalogAdapter implements RoleCatalogCapability
{
    /** @return list<RoleOption> */
    public function listRoles(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Role $role): RoleOption => new RoleOption(
                id: (string) $role->getKey(),
                name: $role->name,
            ))
            ->values()
            ->all();

        return array_values($roles);
    }

    public function findRole(string $roleId): ?RoleOption
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->find($roleId);

        return $role instanceof Role
            ? new RoleOption(id: (string) $role->getKey(), name: $role->name)
            : null;
    }
}
