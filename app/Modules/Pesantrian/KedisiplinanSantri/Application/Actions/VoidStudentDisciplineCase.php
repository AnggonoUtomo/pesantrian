<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VoidStudentDisciplineCase
{
    /** @var list<string> */
    private const FINAL_STATUSES = ['resolved', 'void'];

    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $voidReason, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if (in_array($case->status, self::FINAL_STATUSES, true)) {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan final tidak bisa dibatalkan.',
                ['status' => ['Kasus kedisiplinan resolved atau void sudah final.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.voided',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->voidCase($id, $voidReason, (string) $actorId),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            reason: $voidReason,
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
            'voided_at' => $case->voidedAt,
            'voided_by' => $case->voidedBy,
        ];
    }
}
