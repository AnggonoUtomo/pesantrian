<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class UpsertDormitoryData
{
    public function __construct(
        public string $unitId,
        public string $code,
        public string $name,
        public string $genderPolicy,
        public ?string $description,
        public string $status,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'unit_id' => $this->unitId,
            'code' => $this->code,
            'name' => $this->name,
            'gender_policy' => $this->genderPolicy,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
