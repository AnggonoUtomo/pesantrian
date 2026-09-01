<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\AcceptedAdmissionReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

final readonly class CreateStudentFromAcceptedAdmission
{
    public function __construct(
        private AcceptedAdmissionReader $acceptedAdmissions,
        private StudentActivityPublisher $activities,
        private StudentRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $admissionId,
        ?string $correlationId = null,
    ): StudentData {
        $admission = $this->acceptedAdmissions->findAcceptedForConversion($admissionId);

        if ($admission === null) {
            throw ValidationException::withMessages([
                'admission' => ['Pendaftaran ini belum eligible untuk dikonversi menjadi santri.'],
            ]);
        }

        if ($this->repository->existsForAdmission($admission->admissionId)) {
            throw ValidationException::withMessages([
                'admission' => ['Pendaftaran ini sudah dikonversi menjadi santri.'],
            ]);
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'santri.student.created_from_admission',
            subjectType: 'student',
            mutation: fn (): StudentData => $this->repository->createFromAcceptedAdmission($admission),
            subjectId: static fn (StudentData $student): string => $student->id,
            metadata: static fn (StudentData $student): array => [
                'result' => [
                    'student_no' => $student->studentNo,
                    'admission_id' => $admission->admissionId,
                    'registration_no' => $admission->registrationNo,
                    'full_name' => $student->fullName,
                    'gender' => $student->gender,
                    'primary_unit_id' => $student->primaryUnitId,
                    'entry_date' => $student->entryDate,
                    'status' => $student->status,
                    'guardian_name' => $student->primaryGuardian?->guardianName,
                    'guardian_relation' => $student->primaryGuardian?->guardianRelation,
                    'accepted_at' => $admission->acceptedAt,
                    'accepted_by' => $admission->acceptedBy,
                ],
            ],
            correlationId: $correlationId,
        );
    }
}
