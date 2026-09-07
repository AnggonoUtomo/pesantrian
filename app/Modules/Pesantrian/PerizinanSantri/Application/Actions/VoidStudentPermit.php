<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VoidStudentPermit
{
    /** @var list<string> */
    private const FINAL_STATUSES = ['rejected', 'returned', 'void'];

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

        if (in_array($permit->status, self::FINAL_STATUSES, true)) {
            throw new StudentPermitMutationException(
                'Izin santri yang sudah final tidak bisa dibatalkan.',
                ['status' => ['Izin santri sudah final.']],
            );
        }

        $fromStatus = $permit->status;
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'perizinan_santri.permit.voided',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->void($id, $reason, (string) $actorId),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
                'from_status' => $fromStatus,
                'to_status' => 'void',
                'result' => $permit === null ? null : self::auditResult($permit),
            ],
            reason: $reason,
            correlationId: $correlationId,
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
            'voided_at' => $permit->voidedAt,
            'voided_by' => $permit->voidedBy,
            'revision_count' => $permit->summary->revisionCount,
        ];
    }
}
