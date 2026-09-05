<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryListFilter;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;

interface AsramaReadRepository
{
    public function paginateDormitories(DormitoryListFilter $filter): PaginatedDormitoryData;

    public function findDormitory(string $id): ?DormitoryData;
}
