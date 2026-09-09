<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementSummaryData
{
    public function __construct(
        public bool $isFinal,
        public bool $needsAction,
        public int $revisionCount,
    ) {}

    /** @return array{is_final: bool, needs_action: bool, revision_count: int} */
    public function toArray(): array
    {
        return [
            'is_final' => $this->isFinal,
            'needs_action' => $this->needsAction,
            'revision_count' => $this->revisionCount,
        ];
    }
}
