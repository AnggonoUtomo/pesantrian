<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Actions;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceActivityPublisher;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceMutationRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VoidStudentAttendance
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

        if ($attendance->status === 'void') {
            throw new StudentAttendanceMutationException(
                'Sesi presensi void tidak bisa dibatalkan ulang.',
                ['status' => ['Sesi sudah berstatus void.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'presensi_santri.session.voided',
            subjectType: 'student_attendance_session',
            mutation: fn (): ?StudentAttendanceData => $this->repository->voidSession($id, $reason, (string) $actorId),
            subjectId: static fn (?StudentAttendanceData $attendance): ?string => $attendance?->id,
            metadata: static fn (?StudentAttendanceData $attendance): array => [
                'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
                'result' => $attendance === null ? null : [
                    'status' => $attendance->status,
                    'voided_at' => $attendance->voidedAt,
                    'voided_by' => $attendance->voidedBy,
                    'summary' => $attendance->summary->toArray(),
                ],
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }
}
