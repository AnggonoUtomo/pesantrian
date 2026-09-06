<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceEntryMutationData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentAttendanceEntries
{
    public function __construct(
        private StudentAttendanceActivityPublisher $activities,
        private StudentAttendanceReadRepository $reader,
        private StudentAttendanceMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $entries
     */
    public function execute(?Authenticatable $actor, string $id, array $entries, ?string $correlationId = null): ?StudentAttendanceData
    {
        $attendance = $this->reader->find($id);

        if ($attendance === null) {
            return null;
        }

        $this->ensureDraft($attendance);
        $entryData = $this->normalizeEntries($entries);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'presensi_santri.entry.updated',
            subjectType: 'student_attendance_session',
            mutation: fn (): ?StudentAttendanceData => $this->repository->upsertEntries($id, $entryData),
            subjectId: static fn (?StudentAttendanceData $attendance): ?string => $attendance?->id,
            metadata: static fn (?StudentAttendanceData $attendance): array => [
                'changed_fields' => ['entries'],
                'result' => $attendance === null ? null : [
                    'entry_count' => $attendance->summary->total,
                    'summary' => $attendance->summary->toArray(),
                ],
            ],
            correlationId: $correlationId,
        );
    }

    private function ensureDraft(StudentAttendanceData $attendance): void
    {
        if ($attendance->status === 'draft') {
            return;
        }

        throw new StudentAttendanceMutationException(
            'Sesi presensi yang sudah dikunci tidak bisa diedit langsung.',
            ['status' => ['Sesi submitted, revised, atau void harus memakai jalur lifecycle/revisi.']],
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
}
