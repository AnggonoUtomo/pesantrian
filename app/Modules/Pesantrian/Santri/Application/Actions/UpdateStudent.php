<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudent
{
    public function __construct(
        private StudentActivityPublisher $activities,
        private StudentRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $studentChanges
     * @param  array<string, mixed>  $guardianChanges
     */
    public function execute(
        ?Authenticatable $actor,
        string $id,
        array $studentChanges,
        array $guardianChanges,
        ?string $correlationId = null,
    ): ?StudentData {
        if ($this->repository->find($id) === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'santri.student.updated',
            subjectType: 'student',
            mutation: fn (): ?StudentData => $this->repository->update($id, $studentChanges, $guardianChanges),
            subjectId: static fn (?StudentData $student): ?string => $student?->id,
            metadata: static fn (?StudentData $student): array => [
                'changed_fields' => array_keys([...$studentChanges, ...$guardianChanges]),
                'result' => $student === null ? null : self::auditResult($student),
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
