<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Actions;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationActivityPublisher;
use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateOrganizationUnit
{
    public function __construct(
        private OrganizationActivityPublisher $activities,
        private OrganizationUnitRepository $repository,
    ) {}

    /** @param array<string, string|null> $changes */
    public function execute(
        ?Authenticatable $actor,
        string $id,
        array $changes,
        ?string $correlationId = null,
    ): ?OrganizationUnitData
    {
        if ($this->repository->find($id) === null) {
            return null;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'organization.unit.updated',
            subjectType: 'organization_unit',
            mutation: fn (): OrganizationUnitData => $this->repository->update($id, $changes),
            subjectId: static fn (OrganizationUnitData $unit): string => $unit->id,
            metadata: static fn (OrganizationUnitData $unit): array => [
                'changed_fields' => array_keys($changes),
                'to_status' => $unit->status,
                'result' => [
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'type' => $unit->type,
                    'status' => $unit->status,
                    'parent_id' => $unit->parentId,
                    'location_name' => $unit->locationName,
                ],
            ],
            correlationId: $correlationId,
        );
    }
}
