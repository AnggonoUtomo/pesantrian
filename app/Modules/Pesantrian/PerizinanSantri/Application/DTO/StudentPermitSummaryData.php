<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\DTO;

final readonly class StudentPermitSummaryData
{
    public function __construct(
        public bool $isLate,
        public bool $isFinal,
        public bool $isActive,
        public int $revisionCount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'is_late' => $this->isLate,
            'is_final' => $this->isFinal,
            'is_active' => $this->isActive,
            'revision_count' => $this->revisionCount,
        ];
    }
}
