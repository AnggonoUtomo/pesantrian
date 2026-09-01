<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\UpsertStudentData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudent
{
    public function __construct(
        private StudentActivityPublisher $activities,
        private StudentRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        UpsertStudentData $data,
        ?string $correlationId = null,
    ): StudentData {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'santri.student.created',
            subjectType: 'student',
            mutation: fn (): StudentData => $this->repository->create($data),
            subjectId: static fn (StudentData $student): string => $student->id,
            metadata: static fn (StudentData $student): array => [
                'changed_fields' => [
                    'full_name',
                    'gender',
                    'birth_place',
                    'birth_date',
                    'previous_school',
                    'primary_unit_id',
                    'entry_date',
                    'guardian_name',
                    'guardian_relation',
                    'is_emergency_contact',
                ],
                'result' => self::auditResult($student),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(StudentData $student): array
    {
        return [
            'student_no' => $student->studentNo,
            'full_name' => $student->fullName,
            'gender' => $student->gender,
            'primary_unit_id' => $student->primaryUnitId,
            'entry_date' => $student->entryDate,
            'status' => $student->status,
            'guardian_name' => $student->primaryGuardian?->guardianName,
            'guardian_relation' => $student->primaryGuardian?->guardianRelation,
            'is_emergency_contact' => $student->primaryGuardian?->isEmergencyContact,
        ];
    }
}
