<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

final readonly class RoleOption
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}

    /** @return array{id: string, name: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
