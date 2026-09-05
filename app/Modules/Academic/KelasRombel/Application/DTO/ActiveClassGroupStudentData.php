<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ActiveClassGroupStudentData
{
    public function __construct(
        public string $placementId,
        public string $classGroupId,
        public string $academicTermId,
        public string $studentId,
        public string $studentNo,
        public ?string $studentName,
        public string $joinedOn,
    ) {}
}
