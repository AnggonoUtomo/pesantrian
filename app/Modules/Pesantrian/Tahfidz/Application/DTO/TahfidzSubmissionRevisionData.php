<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class TahfidzSubmissionRevisionData
{
    /** @param array<string, mixed>|null $summary */
    public function __construct(
        public string $id,
        public string $reason,
        public ?string $changedBy,
        public string $changedAt,
        public ?array $summary,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
