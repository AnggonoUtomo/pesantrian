<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodActivityPublisher;
use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CloseAcademicTerm
{
    public function __construct(
        private AcademicPeriodActivityPublisher $activities,
        private AcademicPeriodRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $id,
        ?string $correlationId = null,
    ): ?AcademicTermData {
        if ($this->repository->findTerm($id) === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'academic_period.term.closed',
            subjectType: 'academic_term',
            mutation: fn (): ?AcademicTermData => $this->repository->closeTerm($id),
            subjectId: static fn (AcademicTermData $term): string => $term->id,
            metadata: static fn (AcademicTermData $term): array => [
                'changed_fields' => ['status', 'is_active'],
                'to_status' => $term->status,
                'is_active' => $term->isActive,
                'result' => [
                    'academic_year_id' => $term->academicYearId,
                    'code' => $term->code,
                    'name' => $term->name,
                    'sequence' => $term->sequence,
                    'starts_on' => $term->startsOn,
                    'ends_on' => $term->endsOn,
                    'status' => $term->status,
                    'is_active' => $term->isActive,
                ],
            ],
            correlationId: $correlationId,
        );
    }
}
