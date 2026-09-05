<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class DormitoryRoomPlacementContextData
{
    public function __construct(
        public string $dormitoryId,
        public string $dormitoryUnitId,
        public string $dormitoryStatus,
        public string $genderPolicy,
        public ?string $dormitoryArchivedAt,
        public string $roomId,
        public string $roomCode,
        public int $capacity,
        public int $occupiedCount,
        public string $roomStatus,
        public ?string $roomArchivedAt,
    ) {}

    public function availableCapacity(): int
    {
        return max(0, $this->capacity - $this->occupiedCount);
    }
}
