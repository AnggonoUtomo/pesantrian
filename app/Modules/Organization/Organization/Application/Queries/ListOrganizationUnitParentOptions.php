<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Queries;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitParentOptionData;

final readonly class ListOrganizationUnitParentOptions
{
    public function __construct(private OrganizationUnitRepository $repository) {}

    /** @return list<OrganizationUnitParentOptionData> */
    public function execute(): array
    {
        return $this->repository->activeParentOptions();
    }
}
