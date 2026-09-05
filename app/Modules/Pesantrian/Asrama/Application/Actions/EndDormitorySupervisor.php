<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaSupervisorException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class EndDormitorySupervisor
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $dormitoryId,
        string $assignmentId,
        string $endedAt,
        string $reason,
        ?string $correlationId = null,
    ): ?DormitorySupervisorAssignmentData {
        $assignment = $this->repository->findSupervisorAssignment($assignmentId);

        if (! $assignment instanceof DormitorySupervisorAssignmentData) {
            return null;
        }

        if ($assignment->dormitoryId !== $dormitoryId || $assignment->status !== 'active') {
            throw new AsramaSupervisorException(
                'Akhir tugas musyrif asrama tidak valid.',
                ['assignment' => ['Penugasan aktif tidak ditemukan pada asrama.']],
            );
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.supervisor.ended',
            subjectType: 'dormitory_supervisor_assignment',
            mutation: fn (): ?DormitorySupervisorAssignmentData => $this->repository->endSupervisor($assignment->id, $endedAt, $reason),
            subjectId: static fn (?DormitorySupervisorAssignmentData $ended): ?string => $ended?->id,
            metadata: static fn (?DormitorySupervisorAssignmentData $ended): array => [
                'changed_fields' => ['ended_at', 'status', 'reason'],
                'result' => $ended instanceof DormitorySupervisorAssignmentData ? self::auditResult($ended) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(DormitorySupervisorAssignmentData $assignment): array
    {
        return [
            'employee_id' => $assignment->employeeId,
            'employee_name' => $assignment->employeeName,
            'role' => $assignment->role,
            'dormitory_id' => $assignment->dormitoryId,
            'dormitory_room_id' => $assignment->dormitoryRoomId,
            'started_at' => $assignment->startedAt,
            'ended_at' => $assignment->endedAt,
            'status' => $assignment->status,
            'reason' => $assignment->reason,
        ];
    }
}
