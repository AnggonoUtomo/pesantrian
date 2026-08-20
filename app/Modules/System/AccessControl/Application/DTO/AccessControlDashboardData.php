<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class AccessControlDashboardData
{
    /**
     * @param  list<RoleData>  $roles
     * @param  list<PermissionGroupData>  $permissionGroups
     */
    public function __construct(
        public array $roles,
        public array $permissionGroups,
        public ?string $selectedRoleId,
    ) {}

    /** @return array{roles: list<array{id: string, name: string, guard_name: string, permissions: list<string>, is_protected: bool}>, permissionGroups: list<array{module: string, label: string, permissions: list<array{id: string, name: string, guard_name: string, label: string}>}>, selectedRoleId: string|null} */
    public function toArray(): array
    {
        return [
            'roles' => array_map(static fn (RoleData $role): array => $role->toArray(), $this->roles),
            'permissionGroups' => array_map(
                static fn (PermissionGroupData $group): array => $group->toArray(),
                $this->permissionGroups,
            ),
            'selectedRoleId' => $this->selectedRoleId,
        ];
    }
}
