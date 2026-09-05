<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceData
{
    /** @param list<StudentAttendanceEntryData> $entries */
    public function __construct(
        public string $id,
        public string $attendanceDate,
        public string $contextType,
        public ?string $contextId,
        public string $contextName,
        public string $sessionCode,
        public string $sessionName,
        public string $status,
        public ?string $submittedAt,
        public ?string $submittedBy,
        public ?string $voidedAt,
        public ?string $voidedBy,
        public ?string $voidReason,
        public ?string $createdBy,
        public ?string $createdAt,
        public ?string $updatedAt,
        public StudentAttendanceSummaryData $summary,
        public array $entries = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(bool $includeEntries = true): array
    {
        $data = [
            'id' => $this->id,
            'attendance_date' => $this->attendanceDate,
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'context_name' => $this->contextName,
            'session_code' => $this->sessionCode,
            'session_name' => $this->sessionName,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'submitted_by' => $this->submittedBy,
            'voided_at' => $this->voidedAt,
            'voided_by' => $this->voidedBy,
            'void_reason' => $this->voidReason,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'summary' => $this->summary->toArray(),
        ];

        if ($includeEntries) {
            $data['entries'] = array_map(
                static fn (StudentAttendanceEntryData $entry): array => $entry->toArray(),
                $this->entries,
            );
        }

        return $data;
    }
}
