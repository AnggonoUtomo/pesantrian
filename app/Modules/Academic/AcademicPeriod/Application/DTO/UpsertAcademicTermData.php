<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class UpsertAcademicTermData
{
    public function __construct(
        public string $academicYearId,
        public string $code,
        public string $name,
        public int $sequence,
        public string $startsOn,
        public string $endsOn,
        public string $status,
        public bool $isActive,
    ) {}

    /** @return array<string, string|int|bool> */
    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academicYearId,
            'code' => $this->code,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status,
            'is_active' => $this->isActive,
        ];
    }
}
