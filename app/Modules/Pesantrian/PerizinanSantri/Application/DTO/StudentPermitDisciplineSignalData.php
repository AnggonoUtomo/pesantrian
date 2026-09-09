<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\DTO;

final readonly class StudentPermitDisciplineSignalData
{
    public function __construct(
        public string $permitId,
        public string $permitNo,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $permitType,
        public string $status,
        public string $startsAt,
        public string $endsAt,
        public ?string $returnedAt,
        public string $occurredAt,
        public ?string $destination,
        public ?string $returnNote,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'permit_id' => $this->permitId,
            'permit_no' => $this->permitNo,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'permit_type' => $this->permitType,
            'status' => $this->status,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'returned_at' => $this->returnedAt,
            'occurred_at' => $this->occurredAt,
            'destination' => $this->destination,
            'return_note' => $this->returnNote,
        ];
    }
}
