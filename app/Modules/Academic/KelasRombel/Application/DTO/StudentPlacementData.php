<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class StudentPlacementData
{
    public function __construct(
        public string $id,
        public string $classGroupId,
        public string $academicTermId,
        public string $studentId,
        public string $studentNo,
        public string $joinedOn,
        public ?string $leftOn,
        public string $status,
        public ?string $reason,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
