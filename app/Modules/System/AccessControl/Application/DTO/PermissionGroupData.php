<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class PermissionGroupData
{
    /** @param list<PermissionData> $permissions */
    public function __construct(
        public string $module,
        public string $label,
        public array $permissions,
    ) {}

    /** @return array{module: string, label: string, permissions: list<array{id: string, name: string, guard_name: string, label: string}>} */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'label' => $this->label,
            'permissions' => array_map(
                static fn (PermissionData $permission): array => $permission->toArray(),
                $this->permissions,
            ),
        ];
    }
}
