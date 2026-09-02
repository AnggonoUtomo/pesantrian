<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\HomeroomAssignmentData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelHomeroomException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class EndHomeroom
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $classGroupId, string $homeroomId, string $endedOn, string $reason, ?string $correlationId = null): ?HomeroomAssignmentData
    {
        $homeroom = $this->repository->findHomeroom($homeroomId);

        if (! $homeroom instanceof HomeroomAssignmentData) {
            return null;
        }

        if ($homeroom->classGroupId !== $classGroupId || $homeroom->status !== 'active') {
            throw new KelasRombelHomeroomException(
                'Akhir wali kelas tidak valid.',
                ['homeroom' => ['Wali kelas aktif tidak ditemukan pada rombel.']],
            );
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.homeroom.ended',
            subjectType: 'class_group_homeroom',
            mutation: fn (): ?HomeroomAssignmentData => $this->repository->endHomeroom($homeroom->id, $endedOn, $reason),
            subjectId: static fn (?HomeroomAssignmentData $ended): ?string => $ended?->id,
            metadata: static fn (?HomeroomAssignmentData $ended): array => [
                'changed_fields' => ['ended_on', 'status', 'reason'],
                'result' => $ended instanceof HomeroomAssignmentData ? self::auditResult($ended) : null,
            ],
            reason: $reason,
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
