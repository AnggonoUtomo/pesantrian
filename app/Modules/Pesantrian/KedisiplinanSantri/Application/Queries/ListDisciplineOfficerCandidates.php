<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;

final readonly class ListDisciplineOfficerCandidates
{
    public function __construct(
        private ActiveEmployeeReader $employees,
    ) {}

    public function find(
        string $employeeId,
        ?string $primaryUnitId = null,
        ?string $employmentType = null,
    ): ?ActiveEmployeeOptionData {
        return $this->employees->findActive($employeeId, $primaryUnitId, $employmentType);
    }

    /**
     * @return list<ActiveEmployeeOptionData>
     */
    public function execute(
        ?string $primaryUnitId = null,
        ?string $employmentType = null,
        ?string $search = null,
        int $limit = 50,
    ): array {
        return $this->employees->options($primaryUnitId, $employmentType, $search, $limit);
    }
}
