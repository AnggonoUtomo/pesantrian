<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class ActiveDormitoryResidentData
{
    public function __construct(
        public string $placementId,
        public string $dormitoryId,
        public string $roomId,
        public string $studentId,
        public string $studentNo,
        public ?string $studentName,
        public ?string $roomCode,
        public string $startedAt,
    ) {}
}
