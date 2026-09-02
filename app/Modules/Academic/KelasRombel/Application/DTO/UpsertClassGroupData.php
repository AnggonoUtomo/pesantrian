<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class UpsertClassGroupData
{
    public function __construct(
        public string $academicYearId,
        public string $academicTermId,
        public string $unitId,
        public ?string $curriculumId,
        public string $classLevelId,
        public string $code,
        public string $name,
        public ?int $capacity,
        public string $status,
    ) {}

    /** @return array<string, string|int|null> */
    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academicYearId,
            'academic_term_id' => $this->academicTermId,
            'unit_id' => $this->unitId,
            'curriculum_id' => $this->curriculumId,
            'class_level_id' => $this->classLevelId,
            'code' => $this->code,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'status' => $this->status,
        ];
    }
}
