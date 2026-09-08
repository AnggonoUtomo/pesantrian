<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class UpsertStudentDisciplineCategoryData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public string $defaultSeverity,
        public ?int $defaultPoints,
    ) {}

    /** @return array<string, int|string|null> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'default_severity' => $this->defaultSeverity,
            'default_points' => $this->defaultPoints,
        ];
    }
}
