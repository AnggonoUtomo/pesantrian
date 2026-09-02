<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Contracts;

use App\Modules\Organization\Organization\Application\DTO\EducationUnitOptionData;

interface EducationUnitReader
{
    /** @return list<EducationUnitOptionData> */
    public function options(?string $search = null, int $limit = 50): array;
}
