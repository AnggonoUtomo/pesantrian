<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class DormitorySupervisorAssignmentData
{
    public function __construct(
        public string $id,
        public string $employeeId,
        public string $employeeName,
        public string $role,
        public ?string $dormitoryId,
        public ?string $dormitoryRoomId,
        public ?string $roomCode,
        public string $startedAt,
        public ?string $endedAt,
        public string $status,
        public ?string $reason,
    ) {}
}
