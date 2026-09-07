<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateTahfidzTarget
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    /** @param array<string, int|string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?TahfidzTargetData
    {
        if (array_key_exists('student_id', $changes)) {
            $student = $this->students->findActive((string) $changes['student_id']);

            if ($student === null) {
                throw new TahfidzMutationException('Target hafalan hanya bisa dipindah ke santri aktif.', [
                    'student_id' => ['Santri tidak aktif atau tidak ditemukan.'],
                ]);
            }

            $changes['student_id'] = $student->id;
            $changes['student_no'] = $student->studentNo;
            $changes['student_name'] = $student->fullName;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'tahfidz.target.updated',
            subjectType: 'tahfidz_target',
            mutation: fn (): ?TahfidzTargetData => $this->repository->updateTarget($id, $changes),
            subjectId: static fn (?TahfidzTargetData $target): ?string => $target?->id,
            metadata: static fn (?TahfidzTargetData $target): array => [
                'changed_fields' => array_keys($changes),
                'result' => $target instanceof TahfidzTargetData ? self::auditTarget($target) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditTarget(TahfidzTargetData $target): array
    {
        return [
            'student_id' => $target->studentId,
            'student_no' => $target->studentNo,
            'student_name' => $target->studentName,
            'academic_period_id' => $target->academicPeriodId,
            'period_label' => $target->periodLabel,
            'target_juz' => $target->targetJuz,
            'target_surah' => $target->targetSurah,
            'status' => $target->status,
        ];
    }
}
