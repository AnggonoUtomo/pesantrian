<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class AcademicYearData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $startsOn,
        public string $endsOn,
        public string $status,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
