<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class AssignDormitorySupervisorData
{
    public function __construct(
        public string $employeeId,
        public string $employeeName,
        public string $role,
        public string $dormitoryId,
        public ?string $dormitoryRoomId,
        public string $startedAt,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'role' => $this->role,
            'dormitory_id' => $this->dormitoryId,
            'dormitory_room_id' => $this->dormitoryRoomId,
            'started_at' => $this->startedAt,
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
        ];
    }
}
