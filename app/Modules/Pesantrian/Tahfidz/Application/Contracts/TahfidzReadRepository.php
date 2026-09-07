<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Contracts;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzListFilter;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;

interface TahfidzReadRepository
{
    public function paginate(TahfidzListFilter $filter): PaginatedTahfidzSubmissionData;

    public function findSubmission(string $id): ?TahfidzSubmissionData;
}
