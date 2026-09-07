<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateTahfidzSubmission
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzMutationRepository $repository,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(?Authenticatable $actor, UpsertTahfidzSubmissionData $data, ?string $correlationId = null): TahfidzSubmissionData
    {
        $student = $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new TahfidzMutationException('Setoran tahfidz hanya bisa dibuat untuk santri aktif.', [
                'student_id' => ['Santri tidak aktif atau tidak ditemukan.'],
            ]);
        }

        $supervisorName = null;
        if ($data->supervisorId !== null) {
            $supervisor = $this->employees->findActive($data->supervisorId);

            if ($supervisor === null) {
                throw new TahfidzMutationException('Pembimbing setoran tahfidz harus pegawai aktif.', [
                    'supervisor_id' => ['Pembimbing tidak aktif atau tidak ditemukan.'],
                ]);
            }

            $supervisorName = $supervisor->name;
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $snapshot = new UpsertTahfidzSubmissionData(
            programId: $data->programId,
            targetId: $data->targetId,
            studentId: $student->id,
            studentNo: $student->studentNo,
            studentName: $student->fullName,
            supervisorId: $data->supervisorId,
            supervisorName: $supervisorName,
            submissionDate: $data->submissionDate,
            type: $data->type,
            juz: $data->juz,
            surah: $data->surah,
            ayahFrom: $data->ayahFrom,
            ayahTo: $data->ayahTo,
            status: $data->status,
            qualityNote: $data->qualityNote,
        );

        return $this->activities->publish(
            actorId: $actorId,
            action: 'tahfidz.submission.created',
            subjectType: 'tahfidz_submission',
            mutation: fn (): TahfidzSubmissionData => $this->repository->createSubmission($snapshot, $actorId),
            subjectId: static fn (TahfidzSubmissionData $submission): string => $submission->id,
            metadata: static fn (TahfidzSubmissionData $submission): array => [
                'changed_fields' => ['program_id', 'target_id', 'student_id', 'student_no', 'student_name', 'supervisor_id', 'supervisor_name', 'submission_date', 'type', 'juz', 'surah', 'ayah_from', 'ayah_to', 'status', 'quality_note'],
                'result' => self::auditSubmission($submission),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditSubmission(TahfidzSubmissionData $submission): array
    {
        return [
            'student_id' => $submission->studentId,
            'student_no' => $submission->studentNo,
            'student_name' => $submission->studentName,
            'supervisor_id' => $submission->supervisorId,
            'supervisor_name' => $submission->supervisorName,
            'submission_date' => $submission->submissionDate,
            'type' => $submission->type,
            'juz' => $submission->juz,
            'surah' => $submission->surah,
            'ayah_from' => $submission->ayahFrom,
            'ayah_to' => $submission->ayahTo,
            'status' => $submission->status,
        ];
    }
}
