<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceDisciplineSignalData
{
    public function __construct(
        public string $entryId,
        public string $sessionId,
        public string $sessionCode,
        public string $sessionName,
        public string $attendanceDate,
        public string $contextType,
        public string $contextName,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $status,
        public ?int $minutesLate,
        public ?string $note,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'entry_id' => $this->entryId,
            'session_id' => $this->sessionId,
            'session_code' => $this->sessionCode,
            'session_name' => $this->sessionName,
            'attendance_date' => $this->attendanceDate,
            'context_type' => $this->contextType,
            'context_name' => $this->contextName,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'status' => $this->status,
            'minutes_late' => $this->minutesLate,
            'note' => $this->note,
        ];
    }
}
