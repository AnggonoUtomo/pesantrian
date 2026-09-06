<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ReviseStudentAttendance
{
    public function __construct(
        private StudentAttendanceActivityPublisher $activities,
        private StudentAttendanceReadRepository $reader,
        private StudentAttendanceMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $reason, ?string $correlationId = null): ?StudentAttendanceData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $attendance = $this->reader->find($id);

        if ($attendance === null) {
            return null;
        }

        if (! in_array($attendance->status, ['submitted', 'revised'], true)) {
            throw new StudentAttendanceMutationException(
                'Hanya sesi submitted atau revised yang bisa dibuka untuk revisi.',
                ['status' => ['Sesi draft belum perlu direvisi dan sesi void tidak bisa direvisi.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'presensi_santri.session.revised',
            subjectType: 'student_attendance_session',
            mutation: fn (): ?StudentAttendanceData => $this->repository->reviseSession($id, $reason, (string) $actorId),
            subjectId: static fn (?StudentAttendanceData $attendance): ?string => $attendance?->id,
            metadata: static fn (?StudentAttendanceData $attendance): array => [
                'changed_fields' => ['status', 'revision_reason'],
                'result' => $attendance === null ? null : [
                    'status' => $attendance->status,
                    'summary' => $attendance->summary->toArray(),
                ],
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }
}
