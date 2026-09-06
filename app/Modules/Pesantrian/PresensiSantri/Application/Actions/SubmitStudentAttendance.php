<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SubmitStudentAttendance
{
    public function __construct(
        private StudentAttendanceActivityPublisher $activities,
        private StudentAttendanceReadRepository $reader,
        private StudentAttendanceMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $correlationId = null): ?StudentAttendanceData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $attendance = $this->reader->find($id);

        if ($attendance === null) {
            return null;
        }

        if ($attendance->status !== 'draft') {
            throw new StudentAttendanceMutationException(
                'Hanya sesi draft yang bisa disubmit.',
                ['status' => ['Sesi harus berstatus draft.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'presensi_santri.session.submitted',
            subjectType: 'student_attendance_session',
            mutation: fn (): ?StudentAttendanceData => $this->repository->submitSession($id, (string) $actorId),
            subjectId: static fn (?StudentAttendanceData $attendance): ?string => $attendance?->id,
            metadata: static fn (?StudentAttendanceData $attendance): array => [
                'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
                'result' => $attendance === null ? null : self::auditResult($attendance),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentAttendanceData $attendance): array
    {
        return [
            'status' => $attendance->status,
            'submitted_at' => $attendance->submittedAt,
            'submitted_by' => $attendance->submittedBy,
            'summary' => $attendance->summary->toArray(),
        ];
    }
}
