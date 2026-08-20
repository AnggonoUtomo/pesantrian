<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class PermissionData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $guardName,
        public string $label,
    ) {}

    /** @return array{id: string, name: string, guard_name: string, label: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guardName,
            'label' => $this->label,
        ];
    }
}
