<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Queries;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaReadRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryListFilter;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;

final readonly class ListDormitories
{
    public function __construct(private AsramaReadRepository $repository) {}

    public function execute(DormitoryListFilter $filter): PaginatedDormitoryData
    {
        return $this->repository->paginateDormitories($filter);
    }
}
