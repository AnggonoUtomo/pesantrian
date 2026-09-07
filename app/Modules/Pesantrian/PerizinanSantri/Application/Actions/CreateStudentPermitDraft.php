<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitMutationData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\PrimaryStudentGuardianReader;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

final readonly class CreateStudentPermitDraft
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitMutationRepository $repository,
        private ActiveStudentReader $students,
        private PrimaryStudentGuardianReader $guardians,
    ) {}

    public function execute(?Authenticatable $actor, StudentPermitMutationData $data, ?string $correlationId = null): StudentPermitData
    {
        $normalized = $this->normalize($data);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'perizinan_santri.permit.created',
            subjectType: 'student_permit',
            mutation: fn (): StudentPermitData => $this->repository->createDraft(
                $normalized,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (StudentPermitData $permit): string => $permit->id,
            metadata: static fn (StudentPermitData $permit): array => [
                'changed_fields' => ['student_id', 'permit_type', 'starts_at', 'ends_at', 'destination', 'reason', 'guardian_snapshot'],
                'result' => self::auditResult($permit),
            ],
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentPermitMutationData $data): StudentPermitMutationData
    {
        $student = $data->studentId === null ? null : $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new StudentPermitMutationException(
                'Permohonan izin hanya boleh memakai santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        $guardian = $this->guardians->primaryForActiveStudent($student->id);
        $this->ensureValidRange((string) $data->startsAt, (string) $data->endsAt);

        return new StudentPermitMutationData(
            studentId: $student->id,
            studentNo: $student->studentNo,
            studentName: $student->fullName,
            permitType: $data->permitType,
            startsAt: $data->startsAt,
            endsAt: $data->endsAt,
            destination: $data->destination,
            reason: $data->reason,
            guardianName: $guardian?->guardianName,
            guardianPhone: $guardian?->guardianPhone,
            guardianRelation: $guardian?->guardianRelation,
        );
    }

    private function ensureValidRange(string $startsAt, string $endsAt): void
    {
        if (Carbon::parse($startsAt)->lessThan(Carbon::parse($endsAt))) {
            return;
        }

        throw new StudentPermitMutationException(
            'Rentang waktu izin tidak valid.',
            ['ends_at' => ['Waktu selesai izin harus setelah waktu mulai.']],
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
        ];
    }
}
