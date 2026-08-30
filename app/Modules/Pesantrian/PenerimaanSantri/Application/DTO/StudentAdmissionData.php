<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\DTO;

final readonly class StudentAdmissionData
{
    /**
     * @param  array<int, array<string, string>>|null  $documentChecklist
     */
    public function __construct(
        public string $id,
        public string $registrationNo,
        public ?string $registrationPeriod,
        public string $candidateName,
        public ?string $candidateGender,
        public ?string $candidateBirthPlace,
        public ?string $candidateBirthDate,
        public ?string $previousSchool,
        public ?string $targetUnitId,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public bool $registrationFeeRequired,
        public ?string $registrationFeeAmount,
        public string $registrationFeeStatus,
        public ?array $documentChecklist,
        public string $status,
        public ?string $registeredAt,
        public ?string $decidedAt,
        public ?string $decidedBy,
        public ?string $notes,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'registration_no' => $this->registrationNo,
            'registration_period' => $this->registrationPeriod,
            'candidate_name' => $this->candidateName,
            'candidate_gender' => $this->candidateGender,
            'candidate_birth_place' => $this->candidateBirthPlace,
            'candidate_birth_date' => $this->candidateBirthDate,
            'previous_school' => $this->previousSchool,
            'target_unit_id' => $this->targetUnitId,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'registration_fee_required' => $this->registrationFeeRequired,
            'registration_fee_amount' => $this->registrationFeeAmount,
            'registration_fee_status' => $this->registrationFeeStatus,
            'document_checklist' => $this->documentChecklist,
            'status' => $this->status,
            'registered_at' => $this->registeredAt,
            'decided_at' => $this->decidedAt,
            'decided_by' => $this->decidedBy,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
