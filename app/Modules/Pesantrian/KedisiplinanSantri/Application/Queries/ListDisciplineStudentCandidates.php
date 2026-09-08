<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;

final readonly class ListDisciplineStudentCandidates
{
    public function __construct(
        private ActiveStudentReader $students,
    ) {}

    public function find(string $studentId, ?string $primaryUnitId = null): ?ActiveStudentOptionData
    {
        return $this->students->findActive($studentId, $primaryUnitId);
    }

    /**
     * @return list<ActiveStudentOptionData>
     */
    public function execute(?string $primaryUnitId = null, ?string $search = null, int $limit = 50): array
    {
        return $this->students->options($primaryUnitId, $search, $limit);
    }
}
