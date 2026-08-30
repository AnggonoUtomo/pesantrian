<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionActivityPublisher;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Domain\Services\StudentAdmissionLifecycle;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

final readonly class TransitionStudentAdmission
{
    public function __construct(
        private StudentAdmissionActivityPublisher $activities,
        private StudentAdmissionRepository $repository,
        private StudentAdmissionLifecycle $lifecycle,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $id,
        string $targetStatus,
        ?string $correlationId = null,
    ): ?StudentAdmissionData {
        $admission = $this->repository->find($id);

        if ($admission === null) {
            return null;
        }

        if ($this->lifecycle->isTerminal($admission->status)) {
            throw ValidationException::withMessages([
                'status' => ["Status {$admission->status} bersifat terminal dan tidak dapat diproses lagi."],
            ]);
        }

        if (! $this->lifecycle->canTransition($admission->status, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Status {$admission->status} tidak dapat diubah menjadi {$targetStatus}."],
            ]);
        }

        $action = match ($targetStatus) {
            'verified' => 'penerimaan_santri.registration.verified',
            'accepted' => 'penerimaan_santri.registration.accepted',
            'rejected' => 'penerimaan_santri.registration.rejected',
            'cancelled' => 'penerimaan_santri.registration.cancelled',
            default => 'penerimaan_santri.registration.transitioned',
        };

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: $action,
            subjectType: 'student_admission',
            mutation: fn (): ?StudentAdmissionData => $this->repository->update($id, [
                'status' => $targetStatus,
                'decided_at' => now(),
                'decided_by' => $actor ? (string) $actor->getAuthIdentifier() : null,
            ]),
            subjectId: static fn (?StudentAdmissionData $admission): ?string => $admission?->id,
            metadata: static fn (?StudentAdmissionData $admission): array => [
                'changed_fields' => ['status', 'decided_at', 'decided_by'],
                'to_status' => $admission?->status,
                'result' => $admission === null ? null : self::auditResult($admission),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentAdmissionData $admission): array
    {
        return [
            'registration_no' => $admission->registrationNo,
            'candidate_name' => $admission->candidateName,
            'candidate_gender' => $admission->candidateGender,
            'target_unit_id' => $admission->targetUnitId,
            'guardian_name' => $admission->guardianName,
            'guardian_relation' => $admission->guardianRelation,
            'registration_fee_required' => $admission->registrationFeeRequired,
            'registration_fee_amount' => $admission->registrationFeeAmount,
            'registration_fee_status' => $admission->registrationFeeStatus,
            'status' => $admission->status,
        ];
    }
}
