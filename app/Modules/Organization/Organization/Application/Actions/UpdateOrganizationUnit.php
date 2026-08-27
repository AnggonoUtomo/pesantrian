<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Actions;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;

final readonly class UpdateOrganizationUnit
{
    public function __construct(private OrganizationUnitRepository $repository) {}

    /** @param array<string, string|null> $changes */
    public function execute(string $id, array $changes): ?OrganizationUnitData
    {
        return $this->repository->update($id, $changes);
    }
}
