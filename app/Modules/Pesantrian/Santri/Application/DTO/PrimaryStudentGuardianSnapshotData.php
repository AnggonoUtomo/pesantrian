<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class PrimaryStudentGuardianSnapshotData
{
    public function __construct(
        public string $studentId,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
    ) {}
}
