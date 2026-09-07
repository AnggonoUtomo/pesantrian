<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CheckoutStudentPermit
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitReadRepository $reader,
        private StudentPermitMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $correlationId = null): ?StudentPermitData
    {
        $permit = $this->reader->find($id);

        if ($permit === null) {
            return null;
        }

        if ($permit->status !== 'approved') {
            throw new StudentPermitMutationException(
                'Hanya izin approved yang bisa di-check-out.',
                ['status' => ['Izin santri harus berstatus approved.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'perizinan_santri.permit.checked_out',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->checkout($id, (string) $actorId),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => ['status', 'checked_out_at', 'checked_out_by'],
                'from_status' => 'approved',
                'to_status' => 'checked_out',
                'result' => $permit === null ? null : self::auditResult($permit),
            ],
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
            'checked_out_at' => $permit->checkedOutAt,
            'checked_out_by' => $permit->checkedOutBy,
            'revision_count' => $permit->summary->revisionCount,
        ];
    }
}
