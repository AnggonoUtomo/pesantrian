<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Queries;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitListFilter;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;

final readonly class ListOrganizationUnits
{
    public function __construct(private OrganizationUnitRepository $repository) {}

    public function execute(OrganizationUnitListFilter $filter): PaginatedOrganizationUnitData
    {
        return $this->repository->paginate($filter);
    }
}
