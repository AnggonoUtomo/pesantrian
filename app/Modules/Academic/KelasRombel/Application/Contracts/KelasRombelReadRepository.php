<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Contracts;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupListFilter;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;

interface KelasRombelReadRepository
{
    public function paginateClassGroups(ClassGroupListFilter $filter): PaginatedClassGroupData;

    public function findClassGroup(string $id): ?ClassGroupData;
}
