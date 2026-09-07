<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateTahfidzTarget
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzMutationRepository $repository,
        private ActiveStudentReader $students,
    ) {}

    public function execute(?Authenticatable $actor, UpsertTahfidzTargetData $data, ?string $correlationId = null): TahfidzTargetData
    {
        $student = $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new TahfidzMutationException('Target hafalan hanya bisa dibuat untuk santri aktif.', [
                'student_id' => ['Santri tidak aktif atau tidak ditemukan.'],
            ]);
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $snapshot = new UpsertTahfidzTargetData(
            programId: $data->programId,
            studentId: $student->id,
            studentNo: $student->studentNo,
            studentName: $student->fullName,
            academicPeriodId: $data->academicPeriodId,
            targetJuz: $data->targetJuz,
            targetSurah: $data->targetSurah,
            targetAyahFrom: $data->targetAyahFrom,
            targetAyahTo: $data->targetAyahTo,
            targetNote: $data->targetNote,
            status: $data->status,
        );

        return $this->activities->publish(
            actorId: $actorId,
            action: 'tahfidz.target.created',
            subjectType: 'tahfidz_target',
            mutation: fn (): TahfidzTargetData => $this->repository->createTarget($snapshot, $actorId),
            subjectId: static fn (TahfidzTargetData $target): string => $target->id,
            metadata: static fn (TahfidzTargetData $target): array => [
                'changed_fields' => ['program_id', 'student_id', 'student_no', 'student_name', 'academic_period_id', 'target_juz', 'target_surah', 'target_ayah_from', 'target_ayah_to', 'target_note', 'status'],
                'result' => self::auditTarget($target),
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
