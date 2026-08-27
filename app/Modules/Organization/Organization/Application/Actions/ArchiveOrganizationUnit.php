<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\Actions;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationActivityPublisher;
use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class ArchiveOrganizationUnit
{
    public function __construct(
        private OrganizationActivityPublisher $activities,
        private OrganizationUnitRepository $repository,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $id,
        ?string $correlationId = null,
    ): ?OrganizationUnitData {
        if ($this->repository->find($id) === null) {
            return null;
        }

        if ($this->repository->hasActiveChildren($id)) {
            throw new InvalidArgumentException('Unit organisasi masih memiliki child aktif.');
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'organization.unit.archived',
            subjectType: 'organization_unit',
            mutation: fn (): ?OrganizationUnitData => $this->repository->update($id, ['status' => 'inactive']),
            subjectId: static fn (?OrganizationUnitData $unit): ?string => $unit?->id,
            metadata: static fn (?OrganizationUnitData $unit): array => [
                'changed_fields' => ['status'],
                'to_status' => $unit?->status,
                'result' => $unit === null ? null : [
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
