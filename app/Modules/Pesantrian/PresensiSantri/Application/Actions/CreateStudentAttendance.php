<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceEntryMutationData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSessionMutationData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentAttendance
{
    public function __construct(
        private StudentAttendanceActivityPublisher $activities,
        private StudentAttendanceMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $entries
     */
    public function execute(?Authenticatable $actor, StudentAttendanceSessionMutationData $data, array $entries = [], ?string $correlationId = null): StudentAttendanceData
    {
        $entryData = $this->normalizeEntries($entries);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'presensi_santri.session.created',
            subjectType: 'student_attendance_session',
            mutation: fn (): StudentAttendanceData => $this->repository->createSession(
                $data,
                $entryData,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (StudentAttendanceData $attendance): string => $attendance->id,
            metadata: static fn (StudentAttendanceData $attendance): array => [
                'changed_fields' => [
                    'attendance_date',
                    'context_type',
                    'context_id',
                    'context_name',
                    'session_code',
                    'session_name',
                    'entries',
                ],
                'result' => self::auditResult($attendance),
            ],
            correlationId: $correlationId,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return list<StudentAttendanceEntryMutationData>
     */
    private function normalizeEntries(array $entries): array
    {
        return array_map(function (array $entry): StudentAttendanceEntryMutationData {
            $studentId = (string) $entry['student_id'];
            $student = $this->students->findActive($studentId);

            if ($student === null) {
                throw new StudentAttendanceMutationException(
                    'Entry presensi hanya boleh memakai santri aktif.',
                    ['entries' => ['Santri tidak aktif atau tidak ditemukan.']],
                );
            }

            return new StudentAttendanceEntryMutationData(
                studentId: $student->id,
                studentNo: $student->studentNo,
                studentName: $student->fullName,
                status: (string) $entry['status'],
                minutesLate: isset($entry['minutes_late']) ? (int) $entry['minutes_late'] : null,
                note: isset($entry['note']) ? (string) $entry['note'] : null,
                sourceReferenceType: isset($entry['source_reference_type']) ? (string) $entry['source_reference_type'] : null,
                sourceReferenceId: isset($entry['source_reference_id']) ? (string) $entry['source_reference_id'] : null,
            );
        }, $entries);
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentAttendanceData $attendance): array
    {
        return [
            'attendance_date' => $attendance->attendanceDate,
            'context_type' => $attendance->contextType,
            'context_id' => $attendance->contextId,
            'context_name' => $attendance->contextName,
            'session_code' => $attendance->sessionCode,
            'session_name' => $attendance->sessionName,
            'status' => $attendance->status,
            'entry_count' => $attendance->summary->total,
        ];
    }
}
