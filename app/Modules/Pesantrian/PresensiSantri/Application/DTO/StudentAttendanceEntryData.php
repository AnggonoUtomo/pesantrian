<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceEntryData
{
    public function __construct(
        public string $id,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $status,
        public ?int $minutesLate,
        public ?string $note,
        public ?string $sourceReferenceType,
        public ?string $sourceReferenceId,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'status' => $this->status,
            'minutes_late' => $this->minutesLate,
            'note' => $this->note,
            'source_reference_type' => $this->sourceReferenceType,
            'source_reference_id' => $this->sourceReferenceId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
