<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionActivityPublisher;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\UpsertStudentAdmissionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentAdmission
{
    public function __construct(
        private StudentAdmissionActivityPublisher $activities,
        private StudentAdmissionRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        UpsertStudentAdmissionData $data,
        ?string $correlationId = null,
    ): StudentAdmissionData {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'penerimaan_santri.registration.created',
            subjectType: 'student_admission',
            mutation: fn (): StudentAdmissionData => $this->repository->create($data),
            subjectId: static fn (StudentAdmissionData $admission): string => $admission->id,
            metadata: static fn (StudentAdmissionData $admission): array => [
                'changed_fields' => [
                    'registration_period',
                    'candidate_name',
                    'candidate_gender',
                    'candidate_birth_place',
                    'candidate_birth_date',
                    'previous_school',
                    'target_unit_id',
                    'guardian_name',
                    'guardian_relation',
                    'registration_fee_required',
                    'registration_fee_amount',
                    'registration_fee_status',
                    'document_checklist',
                    'status',
                ],
                'result' => self::auditResult($admission),
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
