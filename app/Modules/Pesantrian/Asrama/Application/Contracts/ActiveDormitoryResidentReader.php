<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use App\Modules\Pesantrian\Asrama\Application\DTO\ActiveDormitoryResidentData;

interface ActiveDormitoryResidentReader
{
    /** @return list<ActiveDormitoryResidentData> */
    public function residentsForDormitory(string $dormitoryId, ?string $roomId = null): array;
}
