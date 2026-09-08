<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ReviewStudentDisciplineCase
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $reviewNote, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if ($case->status !== 'submitted') {
            throw new StudentDisciplineMutationException(
                'Hanya kasus kedisiplinan submitted yang bisa direview.',
                ['status' => ['Kasus kedisiplinan harus berstatus submitted.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.reviewed',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->reviewCase($id, $reviewNote, (string) $actorId),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            reason: $reviewNote,
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditCase(StudentDisciplineCaseData $case): array
    {
        return [
            'case_no' => $case->caseNo,
            'student_no' => $case->studentNo,
            'student_name' => $case->studentName,
            'category_code' => $case->category->code,
            'severity' => $case->severity,
            'status' => $case->status,
            'reviewed_at' => $case->reviewedAt,
            'reviewed_by' => $case->reviewedBy,
        ];
    }
}
