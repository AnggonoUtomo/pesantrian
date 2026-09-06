<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceSessionMutationData
{
    public function __construct(
        public ?string $attendanceDate = null,
        public ?string $contextType = null,
        public ?string $contextId = null,
        public ?string $contextName = null,
        public ?string $sessionCode = null,
        public ?string $sessionName = null,
    ) {}

    /** @return array<string, mixed> */
    public function toDatabasePayload(): array
    {
        return array_filter([
            'attendance_date' => $this->attendanceDate,
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'context_name' => $this->contextName,
            'session_code' => $this->sessionCode === null ? null : mb_strtoupper($this->sessionCode),
            'session_name' => $this->sessionName,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
