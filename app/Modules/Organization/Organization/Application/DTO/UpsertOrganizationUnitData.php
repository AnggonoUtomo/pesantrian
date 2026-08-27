<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class UpsertOrganizationUnitData
{
    public function __construct(
        public ?string $parentId,
        public string $code,
        public string $name,
        public string $type,
        public string $status,
        public ?string $locationName,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'parent_id' => $this->parentId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'location_name' => $this->locationName,
        ];
    }
}
