<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Queries;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\ApprovedStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitAttendanceStatusData;

final readonly class FindApprovedPermitForAttendance
{
    public function __construct(
        private ApprovedStudentPermitReader $permits,
    ) {}

    public function execute(string $studentId, string $attendanceDate): ?StudentPermitAttendanceStatusData
    {
        return $this->permits->approvedForStudentOnDate($studentId, $attendanceDate);
    }

    /**
     * @param  list<string>  $studentIds
     * @return list<StudentPermitAttendanceStatusData>
     */
    public function executeForStudents(array $studentIds, string $attendanceDate): array
    {
        return $this->permits->approvedForStudentsOnDate($studentIds, $attendanceDate);
    }
}
