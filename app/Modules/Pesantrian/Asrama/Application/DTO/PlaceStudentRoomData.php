<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class PlaceStudentRoomData
{
    public function __construct(
        public string $studentId,
        public string $dormitoryRoomId,
        public string $studentNo,
        public string $startedAt,
        public ?string $createdBy,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'dormitory_room_id' => $this->dormitoryRoomId,
            'student_no' => $this->studentNo,
            'started_at' => $this->startedAt,
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
            'active_student_key' => $this->studentId,
            'created_by' => $this->createdBy,
            'ended_by' => null,
        ];
    }
}
