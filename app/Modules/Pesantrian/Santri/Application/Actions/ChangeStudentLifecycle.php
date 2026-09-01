<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ChangeStudentLifecycle
{
    public function __construct(
        private StudentActivityPublisher $activities,
        private StudentRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $studentId,
        string $status,
        ?string $reason,
        ?string $correlationId = null,
    ): ?StudentData {
        $before = $this->repository->find($studentId);

        if ($before === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'santri.student.lifecycle_changed',
            subjectType: 'student',
            mutation: fn (): ?StudentData => $this->repository->changeStatus(
                $studentId,
                $status,
                $reason,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (?StudentData $student): string => $student?->id ?? $studentId,
            metadata: static fn (?StudentData $student): array => [
                'from_status' => $before->status,
                'to_status' => $status,
                'result' => [
                    'student_no' => $student?->studentNo,
                    'full_name' => $student?->fullName,
                    'status' => $student?->status,
                    'status_changed_at' => $student?->statusChangedAt,
                    'status_changed_by' => $student?->statusChangedBy,
                ],
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }
}
