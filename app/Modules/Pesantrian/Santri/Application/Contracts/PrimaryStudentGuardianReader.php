<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Contracts;

use App\Modules\Pesantrian\Santri\Application\DTO\PrimaryStudentGuardianSnapshotData;

interface PrimaryStudentGuardianReader
{
    public function primaryForActiveStudent(string $studentId): ?PrimaryStudentGuardianSnapshotData;
}
