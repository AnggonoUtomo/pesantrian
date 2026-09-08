<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Contracts;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitAttendanceStatusData;

interface ApprovedStudentPermitReader
{
    public function approvedForStudentOnDate(string $studentId, string $attendanceDate): ?StudentPermitAttendanceStatusData;

    /**
     * @param  list<string>  $studentIds
     * @return list<StudentPermitAttendanceStatusData>
     */
    public function approvedForStudentsOnDate(array $studentIds, string $attendanceDate): array;
}
