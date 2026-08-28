<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodActivityPublisher;
use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicYearData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateAcademicYear
{
    public function __construct(
        private AcademicPeriodActivityPublisher $activities,
        private AcademicPeriodRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        UpsertAcademicYearData $data,
        ?string $correlationId = null,
    ): AcademicYearData {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'academic_period.year.created',
            subjectType: 'academic_year',
            mutation: fn (): AcademicYearData => $this->repository->createYear($data),
            subjectId: static fn (AcademicYearData $year): string => $year->id,
            metadata: static fn (AcademicYearData $year): array => [
                'changed_fields' => ['code', 'name', 'starts_on', 'ends_on', 'status'],
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
