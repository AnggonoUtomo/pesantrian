<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Actions;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationActivityPublisher;
use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\UpsertOrganizationUnitData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateOrganizationUnit
{
    public function __construct(
        private OrganizationActivityPublisher $activities,
        private OrganizationUnitRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        UpsertOrganizationUnitData $data,
        ?string $correlationId = null,
    ): OrganizationUnitData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'organization.unit.created',
            subjectType: 'organization_unit',
            mutation: fn (): OrganizationUnitData => $this->repository->create($data),
            subjectId: static fn (OrganizationUnitData $unit): string => $unit->id,
            metadata: static fn (OrganizationUnitData $unit): array => [
                'changed_fields' => ['parent_id', 'code', 'name', 'type', 'status', 'location_name'],
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
