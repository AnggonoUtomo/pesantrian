<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitMutationData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\PrimaryStudentGuardianReader;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

final readonly class UpdateStudentPermitDraft
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitReadRepository $reader,
        private StudentPermitMutationRepository $repository,
        private ActiveStudentReader $students,
        private PrimaryStudentGuardianReader $guardians,
    ) {}

    public function execute(?Authenticatable $actor, string $id, StudentPermitMutationData $data, string $reason, ?string $correlationId = null): ?StudentPermitData
    {
        $permit = $this->reader->find($id);

        if ($permit === null) {
            return null;
        }

        if ($permit->status !== 'draft') {
            throw new StudentPermitMutationException(
                'Permohonan izin yang sudah disubmit tidak bisa diedit langsung.',
                ['status' => ['Permohonan izin harus berstatus draft.']],
            );
        }

        $normalized = $this->normalize($data);
        $this->ensureValidRange($normalized->startsAt ?? $permit->startsAt, $normalized->endsAt ?? $permit->endsAt);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'perizinan_santri.permit.updated',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->updateDraft(
                $id,
                $normalized,
                $reason,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => array_keys($normalized->toDatabasePayload()),
                'result' => $permit === null ? null : [
                    'permit_no' => $permit->permitNo,
                    'student_id' => $permit->studentId,
                    'student_no' => $permit->studentNo,
                    'student_name' => $permit->studentName,
                    'permit_type' => $permit->permitType,
                    'starts_at' => $permit->startsAt,
                    'ends_at' => $permit->endsAt,
                    'status' => $permit->status,
                ],
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentPermitMutationData $data): StudentPermitMutationData
    {
        if ($data->studentId === null) {
            return $data;
        }

        $student = $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new StudentPermitMutationException(
                'Permohonan izin hanya boleh memakai santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        $guardian = $this->guardians->primaryForActiveStudent($student->id);

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
}
