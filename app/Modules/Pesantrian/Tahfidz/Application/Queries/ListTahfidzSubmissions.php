<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Queries;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzListFilter;

final readonly class ListTahfidzSubmissions
{
    public function __construct(private TahfidzReadRepository $repository) {}

    public function execute(TahfidzListFilter $filter): PaginatedTahfidzSubmissionData
    {
        return $this->repository->paginate($filter);
    }
}
