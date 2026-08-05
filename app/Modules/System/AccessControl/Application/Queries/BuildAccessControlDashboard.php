<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Queries;

use App\Modules\System\AccessControl\Application\DTO\AccessControlDashboardData;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;

final class BuildAccessControlDashboard
{
    public function execute(): AccessControlDashboardData
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(static fn (Role $role): array => [
                'id' => (string) $role->getKey(),
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'is_protected' => $role->name === 'SuperSystem',
            ])
            ->values()
            ->all();

        $permissionGroups = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name'])
            ->groupBy(static fn (Permission $permission): string => (string) str($permission->name)->before('.'))
            ->map(static function ($permissions, string $module): array {
                return [
                    'module' => $module,
                    'label' => str($module)->replace('_', ' ')->title()->toString(),
                    'permissions' => $permissions->map(static fn (Permission $permission): array => [
                        'id' => (string) $permission->getKey(),
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'label' => str($permission->name)->after('.')->replace(['.', '_'], ' ')->title()->toString(),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return new AccessControlDashboardData(
            roles: $roles,
            permissionGroups: $permissionGroups,
            selectedRoleId: $roles[0]['id'] ?? null,
        );
    }
}
