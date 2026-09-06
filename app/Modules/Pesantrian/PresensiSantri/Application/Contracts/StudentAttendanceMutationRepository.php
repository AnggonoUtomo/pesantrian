<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Contracts;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceEntryMutationData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSessionMutationData;

interface StudentAttendanceMutationRepository
{
    /** @param list<StudentAttendanceEntryMutationData> $entries */
    public function createSession(StudentAttendanceSessionMutationData $data, array $entries, ?string $actorId): StudentAttendanceData;

    public function updateSession(string $id, StudentAttendanceSessionMutationData $data): ?StudentAttendanceData;

    /** @param list<StudentAttendanceEntryMutationData> $entries */
    public function upsertEntries(string $id, array $entries): ?StudentAttendanceData;
}
