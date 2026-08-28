<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodActivityPublisher;
use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateAcademicYear
{
    public function __construct(
        private AcademicPeriodActivityPublisher $activities,
        private AcademicPeriodRepository $repository,
    ) {}

    /** @param array<string, string> $changes */
    public function execute(
        ?Authenticatable $actor,
        string $id,
        array $changes,
        ?string $correlationId = null,
    ): ?AcademicYearData {
        if ($this->repository->findYear($id) === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'academic_period.year.updated',
            subjectType: 'academic_year',
            mutation: fn (): ?AcademicYearData => $this->repository->updateYear($id, $changes),
            subjectId: static fn (AcademicYearData $year): string => $year->id,
            metadata: static fn (AcademicYearData $year): array => [
                'changed_fields' => array_keys($changes),
                'to_status' => $year->status,
                'result' => [
                    'code' => $year->code,
                    'name' => $year->name,
                    'starts_on' => $year->startsOn,
                    'ends_on' => $year->endsOn,
                    'status' => $year->status,
                ],
            ],
            correlationId: $correlationId,
        );
    }
}
