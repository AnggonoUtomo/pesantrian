<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class TahfidzTargetData
{
    public function __construct(
        public string $id,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public ?string $academicPeriodId,
        public ?string $periodLabel,
        public ?int $targetJuz,
        public ?string $targetSurah,
        public ?int $targetAyahFrom,
        public ?int $targetAyahTo,
        public ?string $targetNote,
        public string $status,
    ) {}
}
