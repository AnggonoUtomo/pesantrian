<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class TahfidzSubmissionSummaryData
{
    public function __construct(
        public bool $hasTarget,
        public bool $hasSupervisor,
        public bool $hasRevision,
        public int $revisionCount,
    ) {}
}
