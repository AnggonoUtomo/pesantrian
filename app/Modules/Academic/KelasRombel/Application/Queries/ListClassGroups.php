<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Queries;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupListFilter;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;

final readonly class ListClassGroups
{
    public function __construct(private KelasRombelReadRepository $repository) {}

    public function execute(ClassGroupListFilter $filter): PaginatedClassGroupData
    {
        return $this->repository->paginateClassGroups($filter);
    }
}
