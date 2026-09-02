<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ClassGroupData
{
    /**
     * @param  list<ClassGroupStudentData>  $students
     * @param  list<ClassGroupHomeroomData>  $homerooms
     */
    public function __construct(
        public string $id,
        public ReferenceData $academicYear,
        public ReferenceData $academicTerm,
        public ReferenceData $unit,
        public ?ReferenceData $curriculum,
        public ReferenceData $classLevel,
        public string $code,
        public string $name,
        public ?int $capacity,
        public string $status,
        public ?string $archivedAt,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $students = [],
        public array $homerooms = [],
    ) {}
}
