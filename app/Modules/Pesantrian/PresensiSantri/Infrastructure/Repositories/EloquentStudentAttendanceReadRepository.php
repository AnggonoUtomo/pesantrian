<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Infrastructure\Repositories;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceEntryData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceListFilter;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSummaryData;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentStudentAttendanceReadRepository implements StudentAttendanceReadRepository
{
    public function paginate(StudentAttendanceListFilter $filter): PaginatedStudentAttendanceData
    {
        $query = $this->filteredQuery($filter)
            ->with('entries')
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('student_attendance_sessions.created_at', 'desc');

        /** @var LengthAwarePaginator<int, StudentAttendanceSessionRecord> $page */
        $page = $query->paginate($filter->perPage, ['student_attendance_sessions.*'], 'page', $filter->page);

        return new PaginatedStudentAttendanceData(
            data: array_map(
                fn (StudentAttendanceSessionRecord $record): StudentAttendanceData => $this->map($record, includeEntries: false),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?StudentAttendanceData
    {
        $record = StudentAttendanceSessionRecord::query()
            ->with(['entries' => fn ($query) => $query->orderBy('student_name')->orderBy('student_no')])
            ->find($id);

        if (! $record instanceof StudentAttendanceSessionRecord) {
            return null;
        }

        return $this->map($record);
    }

    /** @return Builder<StudentAttendanceSessionRecord> */
    private function filteredQuery(StudentAttendanceListFilter $filter): Builder
    {
        return StudentAttendanceSessionRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('session_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('session_code', 'like', '%'.$filter->search.'%')
                        ->orWhere('context_name', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->dateFrom !== null, fn (Builder $query) => $query->whereDate('attendance_date', '>=', $filter->dateFrom))
            ->when($filter->dateTo !== null, fn (Builder $query) => $query->whereDate('attendance_date', '<=', $filter->dateTo))
            ->when($filter->contextType !== null, fn (Builder $query) => $query->where('context_type', $filter->contextType))
            ->when($filter->contextId !== null, fn (Builder $query) => $query->where('context_id', $filter->contextId))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status));
    }

    private function map(StudentAttendanceSessionRecord $record, bool $includeEntries = true): StudentAttendanceData
    {
        /** @var iterable<StudentAttendanceEntryRecord> $entries */
        $entries = $record->entries;
        $entryData = [];
        $counts = [
            'present' => 0,
            'late' => 0,
            'excused' => 0,
            'sick' => 0,
            'absent' => 0,
        ];

        foreach ($entries as $entry) {
            if (array_key_exists($entry->status, $counts)) {
                $counts[$entry->status]++;
            }

            if ($includeEntries) {
                $entryData[] = $this->mapEntry($entry);
            }
        }

        return new StudentAttendanceData(
            id: (string) $record->getKey(),
            attendanceDate: $record->attendance_date->toDateString(),
            contextType: (string) $record->context_type,
            contextId: $record->context_id === null ? null : (string) $record->context_id,
            contextName: (string) $record->context_name,
            sessionCode: (string) $record->session_code,
            sessionName: (string) $record->session_name,
            status: (string) $record->status,
            submittedAt: $record->submitted_at?->toJSON(),
            submittedBy: $record->submitted_by === null ? null : (string) $record->submitted_by,
            voidedAt: $record->voided_at?->toJSON(),
            voidedBy: $record->voided_by === null ? null : (string) $record->voided_by,
            voidReason: $record->void_reason === null ? null : (string) $record->void_reason,
            createdBy: $record->created_by === null ? null : (string) $record->created_by,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            summary: new StudentAttendanceSummaryData(
                total: array_sum($counts),
                present: $counts['present'],
                late: $counts['late'],
                excused: $counts['excused'],
                sick: $counts['sick'],
                absent: $counts['absent'],
            ),
            entries: $entryData,
        );
    }

    private function mapEntry(StudentAttendanceEntryRecord $entry): StudentAttendanceEntryData
    {
        return new StudentAttendanceEntryData(
            id: (string) $entry->getKey(),
            studentId: (string) $entry->student_id,
            studentNo: (string) $entry->student_no,
            studentName: (string) $entry->student_name,
            status: (string) $entry->status,
            minutesLate: $entry->minutes_late === null ? null : (int) $entry->minutes_late,
            note: $entry->note === null ? null : (string) $entry->note,
            sourceReferenceType: $entry->source_reference_type === null ? null : (string) $entry->source_reference_type,
            sourceReferenceId: $entry->source_reference_id === null ? null : (string) $entry->source_reference_id,
            createdAt: $entry->created_at?->toJSON(),
            updatedAt: $entry->updated_at?->toJSON(),
        );
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'attendance_date' => 'student_attendance_sessions.attendance_date',
            'session_code' => 'student_attendance_sessions.session_code',
            'session_name' => 'student_attendance_sessions.session_name',
            'context_type' => 'student_attendance_sessions.context_type',
            'status' => 'student_attendance_sessions.status',
            default => 'student_attendance_sessions.created_at',
        };
    }
}
