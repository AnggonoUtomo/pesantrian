<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Contracts;

use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitListFilter;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitParentOptionData;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\UpsertOrganizationUnitData;

interface OrganizationUnitRepository
{
    public function paginate(OrganizationUnitListFilter $filter): PaginatedOrganizationUnitData;

    public function find(string $id): ?OrganizationUnitData;

    /** @return list<OrganizationUnitParentOptionData> */
    public function activeParentOptions(): array;

    public function hasActiveChildren(string $id): bool;

    public function create(UpsertOrganizationUnitData $data): OrganizationUnitData;

    /** @param array<string, string|null> $changes */
    public function update(string $id, array $changes): ?OrganizationUnitData;
}
