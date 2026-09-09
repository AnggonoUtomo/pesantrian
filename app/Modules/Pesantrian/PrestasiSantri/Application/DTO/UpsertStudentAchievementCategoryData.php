<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class UpsertStudentAchievementCategoryData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
    ) {}

    /** @return array{code: string, name: string, description: string|null} */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
