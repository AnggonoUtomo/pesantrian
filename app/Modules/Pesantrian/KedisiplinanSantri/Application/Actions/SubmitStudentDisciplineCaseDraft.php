<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SubmitStudentDisciplineCaseDraft
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
        private StudentDisciplineCategoryMutationRepository $categories,
        private ActiveStudentReader $students,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if ($case->status !== 'draft') {
            throw new StudentDisciplineMutationException(
                'Hanya kasus kedisiplinan draft yang bisa disubmit.',
                ['status' => ['Kasus kedisiplinan harus berstatus draft.']],
            );
        }

        if ($this->students->findActive($case->studentId) === null) {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan hanya boleh disubmit untuk santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        if ($this->categories->findActiveCategory($case->category->id) === null) {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan wajib memakai kategori aktif.',
                ['category_id' => ['Kategori tidak aktif atau tidak ditemukan.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.submitted',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->submitCaseDraft($id, (string) $actorId),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['status', 'submitted_at'],
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditCase(StudentDisciplineCaseData $case): array
    {
        return [
            'case_no' => $case->caseNo,
            'student_id' => $case->studentId,
            'student_no' => $case->studentNo,
            'student_name' => $case->studentName,
            'category_code' => $case->category->code,
            'severity' => $case->severity,
            'points' => $case->points,
            'status' => $case->status,
            'submitted_at' => $case->submittedAt,
        ];
    }
}
