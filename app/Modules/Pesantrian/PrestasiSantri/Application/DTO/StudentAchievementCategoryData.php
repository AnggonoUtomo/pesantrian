<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementCategoryData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public string $status,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
