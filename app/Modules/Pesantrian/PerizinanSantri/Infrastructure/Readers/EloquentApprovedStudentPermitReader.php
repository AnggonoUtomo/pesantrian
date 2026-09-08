<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Readers;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\ApprovedStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitAttendanceStatusData;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use Illuminate\Support\Carbon;

final class EloquentApprovedStudentPermitReader implements ApprovedStudentPermitReader
{
    /** @var list<string> */
    private const APPROVED_ATTENDANCE_STATUSES = ['approved', 'checked_out', 'returned'];

    public function approvedForStudentOnDate(string $studentId, string $attendanceDate): ?StudentPermitAttendanceStatusData
    {
        $records = $this->approvedForStudentsOnDate([$studentId], $attendanceDate);

        return $records[0] ?? null;
    }

    public function approvedForStudentsOnDate(array $studentIds, string $attendanceDate): array
    {
        $normalizedStudentIds = array_values(array_unique(array_filter(
            array_map(static fn (mixed $studentId): string => (string) $studentId, $studentIds),
            static fn (string $studentId): bool => $studentId !== '',
        )));

        if ($normalizedStudentIds === []) {
            return [];
        }

        $date = Carbon::parse($attendanceDate);
        $dayStart = $date->copy()->startOfDay()->toDateTimeString();
        $dayEnd = $date->copy()->endOfDay()->toDateTimeString();

        /** @var list<StudentPermitRecord> $records */
        $records = StudentPermitRecord::query()
            ->whereIn('student_id', $normalizedStudentIds)
            ->whereIn('status', self::APPROVED_ATTENDANCE_STATUSES)
            ->where('starts_at', '<=', $dayEnd)
            ->where('ends_at', '>=', $dayStart)
            ->orderBy('student_id')
            ->orderBy('starts_at')
            ->get()
            ->all();

        return array_map(
            fn (StudentPermitRecord $record): StudentPermitAttendanceStatusData => $this->map($record, $date),
            $records,
        );
    }

    private function map(StudentPermitRecord $record, Carbon $attendanceDate): StudentPermitAttendanceStatusData
    {
        return new StudentPermitAttendanceStatusData(
            permitId: (string) $record->getKey(),
            permitNo: (string) $record->permit_no,
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            studentName: (string) $record->student_name,
            permitType: (string) $record->permit_type,
            status: (string) $record->status,
            startsAt: $record->starts_at->toJSON(),
            endsAt: $record->ends_at->toJSON(),
            attendanceDate: $attendanceDate->toDateString(),
            destination: $record->destination === null ? null : (string) $record->destination,
            reason: $record->reason === null ? null : (string) $record->reason,
        );
    }
}
