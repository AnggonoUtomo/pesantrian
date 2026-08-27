<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class OrganizationUnitParentOptionData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
    ) {}

    /** @return array{id: string, code: string, name: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
