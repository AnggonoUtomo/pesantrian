<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Infrastructure\Readers;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceDisciplineSignalReader;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceDisciplineSignalData;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use Illuminate\Database\Eloquent\Builder;

final class EloquentStudentAttendanceDisciplineSignalReader implements StudentAttendanceDisciplineSignalReader
{
    /** @var list<string> */
    private const SIGNAL_STATUSES = ['late', 'absent'];

    /** @var list<string> */
    private const REVIEWABLE_SESSION_STATUSES = ['submitted', 'revised'];

    public function signals(?string $dateFrom = null, ?string $dateTo = null, ?string $studentId = null, int $limit = 50): array
    {
        $safeLimit = max(1, min($limit, 100));

        /** @var list<StudentAttendanceEntryRecord> $records */
        $records = StudentAttendanceEntryRecord::query()
            ->with('session')
            ->whereIn('status', self::SIGNAL_STATUSES)
            ->whereHas('session', function (Builder $query) use ($dateFrom, $dateTo): void {
                $query->whereIn('status', self::REVIEWABLE_SESSION_STATUSES)
                    ->when($dateFrom !== null, fn (Builder $query) => $query->whereDate('attendance_date', '>=', $dateFrom))
                    ->when($dateTo !== null, fn (Builder $query) => $query->whereDate('attendance_date', '<=', $dateTo));
            })
            ->when($studentId !== null, fn (Builder $query) => $query->where('student_id', $studentId))
            ->orderByDesc(
                StudentAttendanceSessionRecord::query()
                    ->select('attendance_date')
                    ->whereColumn('student_attendance_sessions.id', 'student_attendance_entries.session_id')
                    ->limit(1)
            )
            ->orderBy('student_name')
            ->limit($safeLimit)
            ->get()
            ->all();

        return array_map(
            static fn (StudentAttendanceEntryRecord $record): StudentAttendanceDisciplineSignalData => new StudentAttendanceDisciplineSignalData(
                entryId: (string) $record->getKey(),
                sessionId: (string) $record->session_id,
                sessionCode: (string) $record->session->session_code,
                sessionName: (string) $record->session->session_name,
                attendanceDate: $record->session->attendance_date->toDateString(),
                contextType: (string) $record->session->context_type,
                contextName: (string) $record->session->context_name,
                studentId: (string) $record->student_id,
                studentNo: (string) $record->student_no,
                studentName: (string) $record->student_name,
                status: (string) $record->status,
                minutesLate: $record->minutes_late === null ? null : (int) $record->minutes_late,
                note: $record->note === null ? null : (string) $record->note,
            ),
            $records,
        );
    }
}
