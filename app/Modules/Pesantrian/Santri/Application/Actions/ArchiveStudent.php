<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Actions;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentActivityPublisher;
use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ArchiveStudent
{
    public function __construct(
        private StudentActivityPublisher $activities,
        private StudentRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $studentId,
        ?string $reason = null,
        ?string $correlationId = null,
    ): ?StudentData {
        if ($this->repository->find($studentId) === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'santri.student.archived',
            subjectType: 'student',
            mutation: fn (): ?StudentData => $this->repository->archive(
                $studentId,
                $actor ? (string) $actor->getAuthIdentifier() : null,
            ),
            subjectId: static fn (?StudentData $student): string => $student?->id ?? $studentId,
            metadata: static fn (?StudentData $student): array => [
                'result' => [
                    'student_no' => $student?->studentNo,
                    'full_name' => $student?->fullName,
                    'status' => $student?->status,
                    'archived_at' => $student?->archivedAt,
                    'archived_by' => $student?->archivedBy,
                ],
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }
}
