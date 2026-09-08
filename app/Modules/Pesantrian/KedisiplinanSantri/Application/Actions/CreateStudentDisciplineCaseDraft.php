<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentDisciplineCaseDraft
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineCaseMutationRepository $repository,
        private StudentDisciplineCategoryMutationRepository $categories,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(?Authenticatable $actor, StudentDisciplineCaseMutationData $data, ?string $correlationId = null): StudentDisciplineCaseData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $normalized = $this->normalize($data);

        return $this->activities->publish(
            actorId: $actorId,
            action: 'kedisiplinan_santri.case.created',
            subjectType: 'student_discipline_case',
            mutation: fn (): StudentDisciplineCaseData => $this->repository->createCaseDraft($normalized, $actorId),
            subjectId: static fn (StudentDisciplineCaseData $case): string => $case->id,
            metadata: static fn (StudentDisciplineCaseData $case): array => [
                'changed_fields' => ['student_id', 'category_id', 'severity', 'points', 'occurred_at', 'location', 'description', 'assigned_employee_id', 'status'],
                'result' => self::auditCase($case),
            ],
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentDisciplineCaseMutationData $data): StudentDisciplineCaseMutationData
    {
        $student = $data->studentId === null ? null : $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan hanya boleh memakai santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        $category = $data->categoryId === null ? null : $this->categories->findActiveCategory($data->categoryId);

        if ($category === null) {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan wajib memakai kategori aktif.',
                ['category_id' => ['Kategori tidak aktif atau tidak ditemukan.']],
            );
        }

        $employee = $data->assignedEmployeeId === null ? null : $this->employees->findActive($data->assignedEmployeeId);

        if ($data->assignedEmployeeId !== null && $employee === null) {
            throw new StudentDisciplineMutationException(
                'Pembina kasus kedisiplinan harus pegawai aktif.',
                ['assigned_employee_id' => ['Pegawai tidak aktif atau tidak ditemukan.']],
            );
        }

        return new StudentDisciplineCaseMutationData(
            studentId: $student->id,
            studentNo: $student->studentNo,
            studentName: $student->fullName,
            unitId: $student->primaryUnitId,
            categoryId: $category->id,
            categoryName: $category->name,
            severity: $data->severity,
            points: $data->points,
            occurredAt: $data->occurredAt,
            location: $data->location,
            description: $data->description,
            assignedEmployeeId: $employee?->id,
            assignedEmployeeName: $employee?->name,
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
        ];
    }
}
