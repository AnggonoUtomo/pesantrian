<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class DormitoryRoomData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public int $capacity,
        public int $occupiedCount,
        public int $availableCapacity,
        public string $status,
        public ?string $archivedAt,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
