<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SubmitStudentPermitDraft
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitReadRepository $reader,
        private StudentPermitMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $correlationId = null): ?StudentPermitData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $permit = $this->reader->find($id);

        if ($permit === null) {
            return null;
        }

        if ($permit->status !== 'draft') {
            throw new StudentPermitMutationException(
                'Hanya permohonan izin draft yang bisa disubmit.',
                ['status' => ['Permohonan izin harus berstatus draft.']],
            );
        }

        if ($this->students->findActive($permit->studentId) === null) {
            throw new StudentPermitMutationException(
                'Permohonan izin hanya boleh disubmit untuk santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        if ($this->repository->hasActiveOverlap($permit->studentId, $permit->startsAt, $permit->endsAt, $permit->id)) {
            throw new StudentPermitMutationException(
                'Rentang izin overlap dengan izin aktif santri yang sama.',
                ['starts_at' => ['Santri sudah memiliki izin aktif pada rentang waktu tersebut.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'perizinan_santri.permit.submitted',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->submitDraft($id, (string) $actorId),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
                'result' => $permit === null ? null : [
                    'permit_no' => $permit->permitNo,
                    'student_id' => $permit->studentId,
                    'student_no' => $permit->studentNo,
                    'student_name' => $permit->studentName,
                    'permit_type' => $permit->permitType,
                    'starts_at' => $permit->startsAt,
                    'ends_at' => $permit->endsAt,
                    'status' => $permit->status,
                    'submitted_at' => $permit->submittedAt,
                    'submitted_by' => $permit->submittedBy,
                ],
            ],
            correlationId: $correlationId,
        );
    }
}
