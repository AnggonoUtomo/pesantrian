<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceEntryMutationData
{
    public function __construct(
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $status,
        public ?int $minutesLate = null,
        public ?string $note = null,
        public ?string $sourceReferenceType = null,
        public ?string $sourceReferenceId = null,
    ) {}
}
