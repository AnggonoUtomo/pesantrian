<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementRevisionData
{
    /** @param array<string, mixed>|null $summary */
    public function __construct(
        public string $id,
        public string $achievementId,
        public ?string $fromStatus,
        public string $toStatus,
        public string $reason,
        public ?string $changedBy,
        public string $changedAt,
        public ?array $summary,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'achievement_id' => $this->achievementId,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'reason' => $this->reason,
            'changed_by' => $this->changedBy,
            'changed_at' => $this->changedAt,
            'summary' => $this->summary,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
