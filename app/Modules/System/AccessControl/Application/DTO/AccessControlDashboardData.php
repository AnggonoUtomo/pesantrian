<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class AccessControlDashboardData
{
    /**
     * @param  array<int, array<string, mixed>>  $roles
     * @param  array<int, array<string, mixed>>  $permissionGroups
     */
    public function __construct(
        public array $roles,
        public array $permissionGroups,
        public ?string $selectedRoleId,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'roles' => $this->roles,
            'permissionGroups' => $this->permissionGroups,
            'selectedRoleId' => $this->selectedRoleId,
        ];
    }
}
