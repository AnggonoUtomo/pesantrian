<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ResolveStudentDisciplineCase
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $resolutionNote, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if ($case->status !== 'action_assigned') {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan hanya bisa diselesaikan setelah tindakan pembinaan ditetapkan.',
                ['status' => ['Kasus kedisiplinan harus berstatus action_assigned.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.resolved',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->resolveCase($id, $resolutionNote, (string) $actorId),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['status', 'resolved_at', 'resolved_by', 'resolution_note'],
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            reason: $resolutionNote,
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
            'status' => $case->status,
            'resolved_at' => $case->resolvedAt,
            'resolved_by' => $case->resolvedBy,
        ];
    }
}
