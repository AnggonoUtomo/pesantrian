<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Infrastructure\Repositories;

use App\Modules\Organization\Organization\Application\Contracts\OrganizationUnitRepository;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitListFilter;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitParentOptionData;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\UpsertOrganizationUnitData;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentOrganizationUnitRepository implements OrganizationUnitRepository
{
    public function paginate(OrganizationUnitListFilter $filter): PaginatedOrganizationUnitData
    {
        $query = OrganizationUnitRecord::query()
            ->when($filter->search !== null, function ($query) use ($filter): void {
                $query->where(function ($query) use ($filter): void {
                    $query->where('name', 'like', '%'.$filter->search.'%')
                        ->orWhere('code', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->status !== null, fn ($query) => $query->where('status', $filter->status))
            ->when($filter->type !== null, fn ($query) => $query->where('type', $filter->type))
            ->orderBy($filter->sortField, $filter->sortDirection);

        /** @var LengthAwarePaginator<int, OrganizationUnitRecord> $page */
        $page = $query->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedOrganizationUnitData(
            data: array_map(
                fn (OrganizationUnitRecord $record): OrganizationUnitData => $this->map($record),
                $page->items(),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?OrganizationUnitData
    {
        $record = OrganizationUnitRecord::query()->find($id);

        return $record instanceof OrganizationUnitRecord ? $this->map($record) : null;
    }

    public function activeParentOptions(): array
    {
        return OrganizationUnitRecord::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(static fn (OrganizationUnitRecord $record): OrganizationUnitParentOptionData => new OrganizationUnitParentOptionData(
                id: (string) $record->getKey(),
                code: (string) $record->code,
                name: (string) $record->name,
            ))
            ->values()
            ->all();
    }

    public function create(UpsertOrganizationUnitData $data): OrganizationUnitData
    {
        /** @var OrganizationUnitRecord $record */
        $record = OrganizationUnitRecord::query()->create($data->toArray());

        return $this->map($record);
    }

    public function update(string $id, array $changes): ?OrganizationUnitData
    {
        $record = OrganizationUnitRecord::query()->find($id);

        if (! $record instanceof OrganizationUnitRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->map($record->refresh());
    }

    private function map(OrganizationUnitRecord $record): OrganizationUnitData
    {
        return new OrganizationUnitData(
            id: (string) $record->getKey(),
            parentId: $record->parent_id === null ? null : (string) $record->parent_id,
            code: (string) $record->code,
            name: (string) $record->name,
            type: (string) $record->type,
            status: (string) $record->status,
            locationName: $record->location_name === null ? null : (string) $record->location_name,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }
}
