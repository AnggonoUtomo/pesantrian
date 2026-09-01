<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class UpsertStudentData
{
    public function __construct(
        public string $fullName,
        public ?string $preferredName,
        public ?string $gender,
        public ?string $birthPlace,
        public ?string $birthDate,
        public ?string $previousSchool,
        public ?string $primaryUnitId,
        public ?string $entryDate,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public bool $isEmergencyContact,
    ) {}

    /** @return array<string, mixed> */
    public function studentAttributes(): array
    {
        return [
            'full_name' => $this->fullName,
            'preferred_name' => $this->preferredName,
            'gender' => $this->gender,
            'birth_place' => $this->birthPlace,
            'birth_date' => $this->birthDate,
            'previous_school' => $this->previousSchool,
            'primary_unit_id' => $this->primaryUnitId,
            'entry_date' => $this->entryDate,
        ];
    }

    /** @return array<string, mixed> */
    public function guardianAttributes(): array
    {
        return [
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'is_primary' => true,
            'is_emergency_contact' => $this->isEmergencyContact,
        ];
    }
}
