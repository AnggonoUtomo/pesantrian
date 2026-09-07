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

    /** @return list<array{id: string, code: string, name: string, description: string|null, status: string}> */
    public function programOptions(int $limit = 100): array;

    /** @return list<array<string, mixed>> */
    public function targetOptions(int $limit = 200): array;
}
