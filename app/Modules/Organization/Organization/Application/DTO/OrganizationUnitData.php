<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class OrganizationUnitData
{
    public function __construct(
        public string $id,
        public ?string $parentId,
        public string $code,
        public string $name,
        public string $type,
        public string $status,
        public ?string $locationName,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'location_name' => $this->locationName,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
