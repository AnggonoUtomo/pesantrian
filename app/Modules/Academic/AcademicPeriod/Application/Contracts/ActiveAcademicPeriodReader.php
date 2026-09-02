<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Contracts;

use App\Modules\Academic\AcademicPeriod\Application\DTO\ActiveAcademicPeriodData;

interface ActiveAcademicPeriodReader
{
    public function current(): ?ActiveAcademicPeriodData;
}
