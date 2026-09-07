<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\DTO;

final readonly class StudentPermitMutationData
{
    public function __construct(
        public ?string $studentId = null,
        public ?string $studentNo = null,
        public ?string $studentName = null,
        public ?string $permitType = null,
        public ?string $startsAt = null,
        public ?string $endsAt = null,
        public ?string $destination = null,
        public ?string $reason = null,
        public ?string $guardianName = null,
        public ?string $guardianPhone = null,
        public ?string $guardianRelation = null,
    ) {}

    /** @return array<string, mixed> */
    public function toDatabasePayload(bool $includeNull = false): array
    {
        $payload = [
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'permit_type' => $this->permitType,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'destination' => $this->destination,
            'reason' => $this->reason,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
        ];

        if ($includeNull) {
            return $payload;
        }

        return array_filter($payload, static fn (mixed $value): bool => $value !== null);
    }
}
