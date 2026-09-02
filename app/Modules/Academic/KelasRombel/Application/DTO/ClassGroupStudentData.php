<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ClassGroupStudentData
{
    public function __construct(
        public string $id,
        public string $studentId,
        public string $studentNo,
        public ?string $studentName,
        public string $joinedOn,
        public ?string $leftOn,
        public string $status,
        public ?string $reason,
    ) {}
}
