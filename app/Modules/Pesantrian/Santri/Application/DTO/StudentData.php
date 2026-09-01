<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class StudentData
{
    /**
     * @param  list<StudentGuardianData>  $guardians
     */
    public function __construct(
        public string $id,
        public string $studentNo,
        public ?string $admissionId,
        public ?string $registrationNo,
        public string $fullName,
        public ?string $preferredName,
        public ?string $gender,
        public ?string $birthPlace,
        public ?string $birthDate,
        public ?string $previousSchool,
        public ?string $primaryUnitId,
        public ?string $entryDate,
        public string $status,
        public ?string $statusReason,
        public ?string $statusChangedAt,
        public ?string $statusChangedBy,
        public ?string $archivedAt,
        public ?string $archivedBy,
        public ?StudentGuardianData $primaryGuardian,
        public array $guardians,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(bool $includeGuardians = true): array
    {
        $data = [
            'id' => $this->id,
            'student_no' => $this->studentNo,
            'admission_id' => $this->admissionId,
            'registration_no' => $this->registrationNo,
            'full_name' => $this->fullName,
            'preferred_name' => $this->preferredName,
            'gender' => $this->gender,
            'birth_place' => $this->birthPlace,
            'birth_date' => $this->birthDate,
            'previous_school' => $this->previousSchool,
            'primary_unit_id' => $this->primaryUnitId,
            'entry_date' => $this->entryDate,
            'status' => $this->status,
            'status_reason' => $this->statusReason,
            'status_changed_at' => $this->statusChangedAt,
            'status_changed_by' => $this->statusChangedBy,
            'archived_at' => $this->archivedAt,
            'archived_by' => $this->archivedBy,
            'primary_guardian' => $this->primaryGuardian?->toArray(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        if ($includeGuardians) {
            $data['guardians'] = array_map(
                static fn (StudentGuardianData $guardian): array => $guardian->toArray(),
                $this->guardians,
            );
        }

        return $data;
    }
}
