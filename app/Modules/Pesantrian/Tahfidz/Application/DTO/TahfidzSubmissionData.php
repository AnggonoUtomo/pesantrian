<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class TahfidzSubmissionData
{
    /** @param list<TahfidzSubmissionRevisionData> $revisions */
    public function __construct(
        public string $id,
        public TahfidzProgramData $program,
        public ?TahfidzTargetData $target,
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
        public ?string $createdBy,
        public ?string $reviewedAt,
        public ?string $reviewedBy,
        public ?string $voidedAt,
        public ?string $voidedBy,
        public ?string $voidReason,
        public ?string $createdAt,
        public ?string $updatedAt,
        public TahfidzSubmissionSummaryData $summary,
        public array $revisions = [],
    ) {}
}
