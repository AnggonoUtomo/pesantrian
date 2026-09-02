<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\AssignHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\HomeroomAssignmentData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelHomeroomException;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class AssignHomeroom
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
        private KelasRombelReadRepository $readRepository,
        private ActiveEmployeeReader $employees,
    ) {}

    public function execute(?Authenticatable $actor, string $classGroupId, string $employeeId, string $assignedOn, ?string $correlationId = null): ?HomeroomAssignmentData
    {
        $classGroup = $this->readRepository->findClassGroup($classGroupId);

        if (! $classGroup instanceof ClassGroupData) {
            return null;
        }

        if ($classGroup->status !== 'active' || $classGroup->archivedAt !== null) {
            throw new KelasRombelHomeroomException(
                'Penetapan wali kelas tidak valid.',
                ['class_group_id' => ['Rombel harus aktif dan belum diarsipkan.']],
            );
        }

        if ($this->repository->findActiveHomeroomForClassGroup($classGroup->id) instanceof HomeroomAssignmentData) {
            throw new KelasRombelHomeroomException(
                'Penetapan wali kelas tidak valid.',
                ['class_group_id' => ['Rombel sudah memiliki wali kelas aktif.']],
            );
        }

        $employee = $this->employees->findActive($employeeId, $classGroup->unit->id, 'teacher');
        if (! $employee instanceof ActiveEmployeeOptionData) {
            throw new KelasRombelHomeroomException(
                'Penetapan wali kelas tidak valid.',
                ['employee_id' => ['Pegawai harus guru aktif pada unit rombel.']],
            );
        }

        $data = new AssignHomeroomData(
            classGroupId: $classGroup->id,
            employeeId: $employee->id,
            employeeName: $employee->name,
            assignedOn: $assignedOn,
        );

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.homeroom.assigned',
            subjectType: 'class_group_homeroom',
            mutation: fn (): HomeroomAssignmentData => $this->repository->assignHomeroom($data),
            subjectId: static fn (HomeroomAssignmentData $homeroom): string => $homeroom->id,
            metadata: static fn (HomeroomAssignmentData $homeroom): array => [
                'changed_fields' => ['class_group_id', 'employee_id', 'employee_name', 'assigned_on', 'status'],
                'result' => self::auditResult($homeroom),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(HomeroomAssignmentData $homeroom): array
    {
        return [
            'class_group_id' => $homeroom->classGroupId,
            'employee_id' => $homeroom->employeeId,
            'employee_name' => $homeroom->employeeName,
            'assigned_on' => $homeroom->assignedOn,
            'ended_on' => $homeroom->endedOn,
            'status' => $homeroom->status,
            'reason' => $homeroom->reason,
        ];
    }
}
