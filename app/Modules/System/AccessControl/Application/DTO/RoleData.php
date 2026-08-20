<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class RoleData
{
    /** @param list<string> $permissions */
    public function __construct(
        public string $id,
        public string $name,
        public string $guardName,
        public array $permissions,
        public bool $isProtected,
    ) {}

    /** @return array{id: string, name: string, guard_name: string, permissions: list<string>, is_protected: bool} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guardName,
            'permissions' => $this->permissions,
            'is_protected' => $this->isProtected,
        ];
    }
}
