<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineSummaryData
{
    public function __construct(
        public bool $isFinal,
        public bool $needsAction,
        public int $revisionCount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'is_final' => $this->isFinal,
            'needs_action' => $this->needsAction,
            'revision_count' => $this->revisionCount,
        ];
    }
}
