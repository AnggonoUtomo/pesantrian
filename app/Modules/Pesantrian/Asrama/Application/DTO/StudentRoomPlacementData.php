<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class StudentRoomPlacementData
{
    public function __construct(
        public string $id,
        public string $studentId,
        public string $dormitoryRoomId,
        public string $studentNo,
        public ?string $studentName,
        public ?string $roomCode,
        public string $startedAt,
        public ?string $endedAt,
        public string $status,
        public ?string $reason,
    ) {}
}
