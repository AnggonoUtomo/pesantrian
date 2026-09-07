<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class UpsertTahfidzTargetData
{
    public function __construct(
        public string $programId,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public ?string $academicPeriodId,
        public ?int $targetJuz,
        public ?string $targetSurah,
        public ?int $targetAyahFrom,
        public ?int $targetAyahTo,
        public ?string $targetNote,
        public string $status,
    ) {}

    /** @return array<string, int|string|null> */
    public function toArray(): array
    {
        return [
            'program_id' => $this->programId,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'academic_period_id' => $this->academicPeriodId,
            'target_juz' => $this->targetJuz,
            'target_surah' => $this->targetSurah,
            'target_ayah_from' => $this->targetAyahFrom,
            'target_ayah_to' => $this->targetAyahTo,
            'target_note' => $this->targetNote,
            'status' => $this->status,
        ];
    }
}
