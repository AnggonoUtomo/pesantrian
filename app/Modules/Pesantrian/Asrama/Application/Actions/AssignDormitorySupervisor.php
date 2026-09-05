<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaReadRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\AssignDormitorySupervisorData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaSupervisorException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class AssignDormitorySupervisor
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
        private AsramaReadRepository $readRepository,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $dormitoryId,
        string $employeeId,
        ?string $roomId,
        string $role,
        string $startedAt,
        ?string $correlationId = null,
    ): ?DormitorySupervisorAssignmentData {
        $dormitory = $this->readRepository->findDormitory($dormitoryId);

        if (! $dormitory instanceof DormitoryData) {
            return null;
        }

        $this->assertDormitoryAcceptsSupervisor($dormitory, $roomId);

        if ($this->repository->findActiveSupervisorForScope($employeeId, $dormitory->id, $roomId) instanceof DormitorySupervisorAssignmentData) {
            throw new AsramaSupervisorException(
                'Penugasan musyrif asrama tidak valid.',
                ['employee_id' => ['Pegawai sudah memiliki penugasan aktif pada scope asrama/kamar ini.']],
            );
        }

        $employee = $this->employees->findActive($employeeId, $dormitory->unit->id);
        if (! $employee instanceof ActiveEmployeeOptionData) {
            throw new AsramaSupervisorException(
                'Penugasan musyrif asrama tidak valid.',
                ['employee_id' => ['Pegawai harus aktif pada unit asrama.']],
            );
        }

        $data = new AssignDormitorySupervisorData(
            employeeId: $employee->id,
            employeeName: $employee->name,
            role: $role,
            dormitoryId: $dormitory->id,
            dormitoryRoomId: $roomId,
            startedAt: $startedAt,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.supervisor.assigned',
            subjectType: 'dormitory_supervisor_assignment',
            mutation: fn (): DormitorySupervisorAssignmentData => $this->repository->assignSupervisor($data),
            subjectId: static fn (DormitorySupervisorAssignmentData $assignment): string => $assignment->id,
            metadata: static fn (DormitorySupervisorAssignmentData $assignment): array => [
                'changed_fields' => ['employee_id', 'employee_name', 'role', 'dormitory_id', 'dormitory_room_id', 'started_at', 'status'],
                'result' => self::auditResult($assignment),
            ],
            correlationId: $correlationId,
        );
    }

    private function assertDormitoryAcceptsSupervisor(DormitoryData $dormitory, ?string $roomId): void
    {
        if ($dormitory->status !== 'active' || $dormitory->archivedAt !== null) {
            throw new AsramaSupervisorException(
                'Penugasan musyrif asrama tidak valid.',
                ['dormitory_id' => ['Asrama harus aktif dan belum diarsipkan.']],
            );
        }

        if ($roomId === null) {
            return;
        }

        $room = collect($dormitory->rooms)->first(static fn (DormitoryRoomData $room): bool => $room->id === $roomId);

        if (! $room instanceof DormitoryRoomData || $room->status !== 'active' || $room->archivedAt !== null) {
            throw new AsramaSupervisorException(
                'Penugasan musyrif asrama tidak valid.',
                ['dormitory_room_id' => ['Kamar harus aktif, belum diarsipkan, dan berada pada asrama ini.']],
            );
        }
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
