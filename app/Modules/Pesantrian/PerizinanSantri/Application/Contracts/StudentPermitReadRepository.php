<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Contracts;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitListFilter;

interface StudentPermitReadRepository
{
    public function paginate(StudentPermitListFilter $filter): PaginatedStudentPermitData;

    public function find(string $id): ?StudentPermitData;
}
