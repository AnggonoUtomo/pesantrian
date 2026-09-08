<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class AssignStudentDisciplineCaseAction
{
    /** @var list<string> */
    private const ALLOWED_STATUSES = ['submitted', 'in_review'];

    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $actionPlan, ?string $assignedEmployeeId, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if (! in_array($case->status, self::ALLOWED_STATUSES, true)) {
            throw new StudentDisciplineMutationException(
                'Tindakan pembinaan hanya bisa ditetapkan dari kasus submitted atau in review.',
                ['status' => ['Kasus kedisiplinan harus berstatus submitted atau in_review.']],
            );
        }

        $employee = $assignedEmployeeId === null ? null : $this->employees->findActive($assignedEmployeeId);

        if ($assignedEmployeeId !== null && $employee === null) {
            throw new StudentDisciplineMutationException(
                'Pembina kasus kedisiplinan harus pegawai aktif.',
                ['assigned_employee_id' => ['Pegawai tidak aktif atau tidak ditemukan.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.action_assigned',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->assignCaseAction(
                $id,
                $actionPlan,
                $employee?->id,
                $employee?->name,
                (string) $actorId,
            ),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['status', 'action_plan', 'action_assigned_at', 'assigned_employee_id'],
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            reason: $actionPlan,
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
            'assigned_employee_id' => $case->assignedEmployeeId,
            'assigned_employee_name' => $case->assignedEmployeeName,
            'action_assigned_at' => $case->actionAssignedAt,
        ];
    }
}
