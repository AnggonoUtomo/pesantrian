<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineActivityPublisher;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentDisciplineCaseDraft
{
    public function __construct(
        private StudentDisciplineActivityPublisher $activities,
        private StudentDisciplineReadRepository $reader,
        private StudentDisciplineCaseMutationRepository $repository,
        private StudentDisciplineCategoryMutationRepository $categories,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(?Authenticatable $actor, string $id, StudentDisciplineCaseMutationData $data, string $reason, ?string $correlationId = null): ?StudentDisciplineCaseData
    {
        $case = $this->reader->findCase($id);

        if ($case === null) {
            return null;
        }

        if ($case->status !== 'draft') {
            throw new StudentDisciplineMutationException(
                'Kasus kedisiplinan yang sudah disubmit tidak bisa diedit langsung.',
                ['status' => ['Kasus kedisiplinan harus berstatus draft.']],
            );
        }

        $normalized = $this->normalize($data);

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kedisiplinan_santri.case.updated',
            subjectType: 'student_discipline_case',
            mutation: fn (): ?StudentDisciplineCaseData => $this->repository->updateCaseDraft(
                $id,
                $normalized,
                $reason,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (?StudentDisciplineCaseData $case): ?string => $case?->id,
            metadata: static fn (?StudentDisciplineCaseData $case): array => [
                'changed_fields' => array_keys($normalized->toDatabasePayload()),
                'result' => $case instanceof StudentDisciplineCaseData ? self::auditCase($case) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentDisciplineCaseMutationData $data): StudentDisciplineCaseMutationData
    {
        $studentId = $data->studentId;
        $studentNo = null;
        $studentName = null;
        $unitId = null;

        if ($studentId !== null) {
            $student = $this->students->findActive($studentId);

            if ($student === null) {
                throw new StudentDisciplineMutationException(
                    'Kasus kedisiplinan hanya boleh memakai santri aktif.',
                    ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
                );
            }

            $studentId = $student->id;
            $studentNo = $student->studentNo;
            $studentName = $student->fullName;
            $unitId = $student->primaryUnitId;
        }

        $categoryId = $data->categoryId;
        $categoryName = null;

        if ($categoryId !== null) {
            $category = $this->categories->findActiveCategory($categoryId);

            if ($category === null) {
                throw new StudentDisciplineMutationException(
                    'Kasus kedisiplinan wajib memakai kategori aktif.',
                    ['category_id' => ['Kategori tidak aktif atau tidak ditemukan.']],
                );
            }

            $categoryId = $category->id;
            $categoryName = $category->name;
        }

        $assignedEmployeeId = $data->assignedEmployeeId;
        $assignedEmployeeName = null;

        if ($assignedEmployeeId !== null) {
            $employee = $this->employees->findActive($assignedEmployeeId);

            if ($employee === null) {
                throw new StudentDisciplineMutationException(
                    'Pembina kasus kedisiplinan harus pegawai aktif.',
                    ['assigned_employee_id' => ['Pegawai tidak aktif atau tidak ditemukan.']],
                );
            }

            $assignedEmployeeId = $employee->id;
            $assignedEmployeeName = $employee->name;
        }

        return new StudentDisciplineCaseMutationData(
            studentId: $studentId,
            studentNo: $studentNo,
            studentName: $studentName,
            unitId: $unitId,
            categoryId: $categoryId,
            categoryName: $categoryName,
            severity: $data->severity,
            points: $data->points,
            occurredAt: $data->occurredAt,
            location: $data->location,
            description: $data->description,
            assignedEmployeeId: $assignedEmployeeId,
            assignedEmployeeName: $assignedEmployeeName,
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
