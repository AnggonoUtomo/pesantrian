<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\DTO\AccessControlDashboardData;
use App\Modules\System\AccessControl\Application\DTO\PaginatedPermissionData;
use App\Modules\System\AccessControl\Application\DTO\PaginatedRoleData;
use App\Modules\System\AccessControl\Application\DTO\PermissionData;
use App\Modules\System\AccessControl\Application\DTO\PermissionGroupData;
use App\Modules\System\AccessControl\Application\DTO\PermissionListFilter;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\DTO\RoleListFilter;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;

final class EloquentAccessControlReadRepository implements AccessControlReadRepository
{
    public function dashboard(): AccessControlDashboardData
    {
        $roles = [];

        foreach (Role::query()->with('permissions:id,name')->orderBy('name')->get() as $role) {
            $permissions = [];

            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->name;
            }

            $roles[] = new RoleData(
                id: (string) $role->getKey(),
                name: $role->name,
                guardName: $role->guard_name,
                permissions: $permissions,
                isProtected: $role->name === 'SuperSystem',
            );
        }

        /** @var array<string, list<PermissionData>> $permissionsByModule */
        $permissionsByModule = [];

        foreach (Permission::query()->orderBy('name')->get(['id', 'name', 'guard_name']) as $permission) {
            $module = (string) str($permission->name)->before('.');
            $permissionsByModule[$module][] = new PermissionData(
                id: (string) $permission->getKey(),
                name: $permission->name,
                guardName: $permission->guard_name,
                label: str($permission->name)->after('.')->replace(['.', '_'], ' ')->title()->toString(),
            );
        }

        $permissionGroups = [];

        foreach ($permissionsByModule as $module => $permissions) {
            $permissionGroups[] = new PermissionGroupData(
                module: $module,
                label: str($module)->replace('_', ' ')->title()->toString(),
                permissions: $permissions,
            );
        }

        return new AccessControlDashboardData(
            roles: $roles,
            permissionGroups: $permissionGroups,
            selectedRoleId: $roles[0]->id ?? null,
        );
    }

    public function paginateRoles(RoleListFilter $filter): PaginatedRoleData
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->when($filter->search !== null, static function ($query) use ($filter): void {
                $query->where('name', 'like', '%'.$filter->search.'%');
            })
            ->orderBy('name', $filter->sortDirection)
            ->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedRoleData(
            data: array_values($roles->getCollection()->map(
                fn (Role $role): RoleData => $this->mapRole($role),
            )->all()),
            total: $roles->total(),
            currentPage: $roles->currentPage(),
            lastPage: $roles->lastPage(),
            perPage: $roles->perPage(),
        );
    }

    public function findRole(string $roleId): ?RoleData
    {
        $role = Role::query()->with('permissions:id,name')->find($roleId);

        return $role instanceof Role ? $this->mapRole($role) : null;
    }

    public function paginatePermissions(PermissionListFilter $filter): PaginatedPermissionData
    {
        $permissions = Permission::query()
            ->when($filter->search !== null, static function ($query) use ($filter): void {
                $query->where('name', 'like', '%'.$filter->search.'%');
            })
            ->when($filter->module !== null, static function ($query) use ($filter): void {
                $query->where('name', 'like', $filter->module.'.%');
            })
            ->orderBy('name', $filter->sortDirection)
            ->paginate($filter->perPage, ['id', 'name', 'guard_name'], 'page', $filter->page);

        return new PaginatedPermissionData(
            data: array_values($permissions->getCollection()->map(
                fn (Permission $permission): PermissionData => $this->mapPermission($permission),
            )->all()),
            total: $permissions->total(),
            currentPage: $permissions->currentPage(),
            lastPage: $permissions->lastPage(),
            perPage: $permissions->perPage(),
        );
    }

    private function mapRole(Role $role): RoleData
    {
        $permissions = [];

        foreach ($role->permissions as $permission) {
            $permissions[] = $permission->name;
        }

        return new RoleData(
            id: (string) $role->getKey(),
            name: $role->name,
            guardName: $role->guard_name,
            permissions: $permissions,
            isProtected: $role->name === 'SuperSystem',
        );
    }

    private function mapPermission(Permission $permission): PermissionData
    {
        return new PermissionData(
            id: (string) $permission->getKey(),
            name: $permission->name,
            guardName: $permission->guard_name,
            label: str($permission->name)->after('.')->replace(['.', '_'], ' ')->title()->toString(),
        );
    }
}
