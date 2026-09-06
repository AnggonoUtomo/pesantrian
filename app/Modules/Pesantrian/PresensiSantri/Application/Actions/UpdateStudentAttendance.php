<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSessionMutationData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentAttendance
{
    public function __construct(
        private StudentAttendanceActivityPublisher $activities,
        private StudentAttendanceReadRepository $reader,
        private StudentAttendanceMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, StudentAttendanceSessionMutationData $data, ?string $correlationId = null): ?StudentAttendanceData
    {
        $attendance = $this->reader->find($id);

        if ($attendance === null) {
            return null;
        }

        $this->ensureDraft($attendance);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'presensi_santri.session.updated',
            subjectType: 'student_attendance_session',
            mutation: fn (): ?StudentAttendanceData => $this->repository->updateSession($id, $data),
            subjectId: static fn (?StudentAttendanceData $attendance): ?string => $attendance?->id,
            metadata: static fn (?StudentAttendanceData $attendance): array => [
                'changed_fields' => array_keys($data->toDatabasePayload()),
                'result' => $attendance === null ? null : [
                    'attendance_date' => $attendance->attendanceDate,
                    'context_type' => $attendance->contextType,
                    'context_id' => $attendance->contextId,
                    'context_name' => $attendance->contextName,
                    'session_code' => $attendance->sessionCode,
                    'session_name' => $attendance->sessionName,
                    'status' => $attendance->status,
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
}
