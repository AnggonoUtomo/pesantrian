<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class AcademicTermData
{
    public function __construct(
        public string $id,
        public string $academicYearId,
        public string $code,
        public string $name,
        public int $sequence,
        public string $startsOn,
        public string $endsOn,
        public string $status,
        public bool $isActive,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, string|int|bool|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academicYearId,
            'code' => $this->code,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
