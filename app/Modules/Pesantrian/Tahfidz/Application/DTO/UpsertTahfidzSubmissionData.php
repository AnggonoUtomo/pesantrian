<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class UpsertTahfidzSubmissionData
{
    public function __construct(
        public string $programId,
        public ?string $targetId,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public ?string $supervisorId,
        public ?string $supervisorName,
        public string $submissionDate,
        public string $type,
        public ?int $juz,
        public ?string $surah,
        public ?int $ayahFrom,
        public ?int $ayahTo,
        public string $status,
        public ?string $qualityNote,
    ) {}

    /** @return array<string, int|string|null> */
    public function toArray(): array
    {
        return [
            'program_id' => $this->programId,
            'target_id' => $this->targetId,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'supervisor_id' => $this->supervisorId,
            'supervisor_name' => $this->supervisorName,
            'submission_date' => $this->submissionDate,
            'type' => $this->type,
            'juz' => $this->juz,
            'surah' => $this->surah,
            'ayah_from' => $this->ayahFrom,
            'ayah_to' => $this->ayahTo,
            'status' => $this->status,
            'quality_note' => $this->qualityNote,
        ];
    }
}
