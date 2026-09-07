<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RejectStudentPermit
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitReadRepository $reader,
        private StudentPermitMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $reason, ?string $correlationId = null): ?StudentPermitData
    {
        $permit = $this->reader->find($id);

        if ($permit === null) {
            return null;
        }

        $this->ensureSubmitted($permit);

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'perizinan_santri.permit.rejected',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->reject($id, $reason, (string) $actorId),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
                'from_status' => 'submitted',
                'to_status' => 'rejected',
                'result' => $permit === null ? null : self::auditResult($permit),
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function ensureSubmitted(StudentPermitData $permit): void
    {
        if ($permit->status === 'submitted') {
            return;
        }

        throw new StudentPermitMutationException(
            'Hanya permohonan izin submitted yang bisa ditolak.',
            ['status' => ['Permohonan izin harus berstatus submitted.']],
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentPermitData $permit): array
    {
        return [
            'permit_no' => $permit->permitNo,
            'student_id' => $permit->studentId,
            'student_no' => $permit->studentNo,
            'student_name' => $permit->studentName,
            'permit_type' => $permit->permitType,
            'starts_at' => $permit->startsAt,
            'ends_at' => $permit->endsAt,
            'status' => $permit->status,
            'reviewed_at' => $permit->reviewedAt,
            'reviewed_by' => $permit->reviewedBy,
            'revision_count' => $permit->summary->revisionCount,
        ];
    }
}
