<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateTahfidzSubmission
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzReadRepository $reader,
        private TahfidzMutationRepository $repository,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
    ) {}

    /** @param array<string, int|string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?TahfidzSubmissionData
    {
        $submission = $this->reader->findSubmission($id);

        if ($submission === null) {
            return null;
        }

        $this->ensureEditable($submission);
        $this->hydrateSnapshots($changes);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'tahfidz.submission.updated',
            subjectType: 'tahfidz_submission',
            mutation: fn (): ?TahfidzSubmissionData => $this->repository->updateSubmission($id, $changes),
            subjectId: static fn (?TahfidzSubmissionData $submission): ?string => $submission?->id,
            metadata: static fn (?TahfidzSubmissionData $submission): array => [
                'changed_fields' => array_keys($changes),
                'result' => $submission instanceof TahfidzSubmissionData ? self::auditSubmission($submission) : null,
            ],
            correlationId: $correlationId,
        );
    }

    private function ensureEditable(TahfidzSubmissionData $submission): void
    {
        if (in_array($submission->status, ['draft', 'submitted'], true)) {
            return;
        }

        throw new TahfidzMutationException('Setoran tahfidz yang sudah final atau dibatalkan tidak bisa diedit langsung.', [
            'status' => ['Gunakan jalur review, koreksi, atau void yang sesuai.'],
        ]);
    }

    /** @param array<string, int|string|null> $changes */
    private function hydrateSnapshots(array &$changes): void
    {
        if (array_key_exists('student_id', $changes)) {
            $student = $this->students->findActive((string) $changes['student_id']);

            if ($student === null) {
                throw new TahfidzMutationException('Setoran tahfidz hanya bisa dipindah ke santri aktif.', [
                    'student_id' => ['Santri tidak aktif atau tidak ditemukan.'],
                ]);
            }

            $changes['student_id'] = $student->id;
            $changes['student_no'] = $student->studentNo;
            $changes['student_name'] = $student->fullName;
        }

        if (array_key_exists('supervisor_id', $changes)) {
            if ($changes['supervisor_id'] === null) {
                $changes['supervisor_name'] = null;

                return;
            }

            $supervisor = $this->employees->findActive((string) $changes['supervisor_id']);

            if ($supervisor === null) {
                throw new TahfidzMutationException('Pembimbing setoran tahfidz harus pegawai aktif.', [
                    'supervisor_id' => ['Pembimbing tidak aktif atau tidak ditemukan.'],
                ]);
            }

            $changes['supervisor_id'] = $supervisor->id;
            $changes['supervisor_name'] = $supervisor->name;
        }
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
