<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCategoryData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public string $defaultSeverity,
        public ?int $defaultPoints,
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
            'default_severity' => $this->defaultSeverity,
            'default_points' => $this->defaultPoints,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
