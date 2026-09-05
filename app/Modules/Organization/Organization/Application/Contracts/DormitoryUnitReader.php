<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Contracts;

use App\Modules\Organization\Organization\Application\DTO\DormitoryUnitOptionData;

interface DormitoryUnitReader
{
    /** @return list<DormitoryUnitOptionData> */
    public function options(?string $search = null, int $limit = 50): array;
}
