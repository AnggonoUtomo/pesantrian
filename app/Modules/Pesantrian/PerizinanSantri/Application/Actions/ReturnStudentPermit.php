<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Actions;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitActivityPublisher;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitMutationRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

final readonly class ReturnStudentPermit
{
    public function __construct(
        private StudentPermitActivityPublisher $activities,
        private StudentPermitReadRepository $reader,
        private StudentPermitMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $returnedAt, ?string $returnNote = null, ?string $correlationId = null): ?StudentPermitData
    {
        $permit = $this->reader->find($id);

        if ($permit === null) {
            return null;
        }

        if ($permit->status !== 'checked_out') {
            throw new StudentPermitMutationException(
                'Hanya izin checked_out yang bisa dicatat kembali.',
                ['status' => ['Izin santri harus berstatus checked_out.']],
            );
        }

        if ($permit->checkedOutAt !== null && Carbon::parse($returnedAt)->isBefore(Carbon::parse($permit->checkedOutAt))) {
            throw new StudentPermitMutationException(
                'Waktu kembali tidak boleh lebih awal dari waktu check-out.',
                ['returned_at' => ['Waktu kembali harus sama atau setelah waktu check-out.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'perizinan_santri.permit.returned',
            subjectType: 'student_permit',
            mutation: fn (): ?StudentPermitData => $this->repository->returnPermit($id, $returnedAt, $returnNote, (string) $actorId),
            subjectId: static fn (?StudentPermitData $permit): ?string => $permit?->id,
            metadata: static fn (?StudentPermitData $permit): array => [
                'changed_fields' => ['status', 'returned_at', 'returned_by', 'return_note'],
                'from_status' => 'checked_out',
                'to_status' => 'returned',
                'result' => $permit === null ? null : self::auditResult($permit),
            ],
            reason: $returnNote,
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
            'returned_at' => $permit->returnedAt,
            'returned_by' => $permit->returnedBy,
            'is_late' => $permit->summary->isLate,
            'revision_count' => $permit->summary->revisionCount,
        ];
    }
}
