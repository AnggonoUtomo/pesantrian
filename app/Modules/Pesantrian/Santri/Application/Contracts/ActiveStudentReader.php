<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Contracts;

use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;

interface ActiveStudentReader
{
    public function findActive(string $studentId, ?string $primaryUnitId = null): ?ActiveStudentOptionData;

    /** @return list<ActiveStudentOptionData> */
    public function options(?string $primaryUnitId = null, ?string $search = null, int $limit = 50): array;
}
