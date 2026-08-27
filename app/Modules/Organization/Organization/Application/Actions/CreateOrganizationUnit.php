<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Actions;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\UpsertOrganizationUnitData;

final readonly class CreateOrganizationUnit
{
    public function __construct(private OrganizationUnitRepository $repository) {}

    public function execute(UpsertOrganizationUnitData $data): OrganizationUnitData
    {
        return $this->repository->create($data);
    }
}
