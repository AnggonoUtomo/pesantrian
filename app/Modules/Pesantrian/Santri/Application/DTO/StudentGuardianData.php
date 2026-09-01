<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class StudentGuardianData
{
    public function __construct(
        public string $id,
        public string $studentId,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public bool $isPrimary,
        public bool $isEmergencyContact,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentId,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'is_primary' => $this->isPrimary,
            'is_emergency_contact' => $this->isEmergencyContact,
        ];
    }
}
