<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\DTO;

final readonly class AcceptedAdmissionData
{
    public function __construct(
        public string $admissionId,
        public string $registrationNo,
        public string $candidateName,
        public ?string $candidateGender,
        public ?string $candidateBirthPlace,
        public ?string $candidateBirthDate,
        public ?string $previousSchool,
        public ?string $targetUnitId,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public ?string $acceptedAt,
        public ?string $acceptedBy,
    ) {}
}
