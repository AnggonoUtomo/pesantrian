<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Infrastructure\Readers;

use App\Modules\Organization\Organization\Application\Contracts\DormitoryUnitReader;
use App\Modules\Organization\Organization\Application\DTO\DormitoryUnitOptionData;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;

final class EloquentDormitoryUnitReader implements DormitoryUnitReader
{
    public function options(?string $search = null, int $limit = 50): array
    {
        $safeLimit = max(1, min($limit, 100));

        return OrganizationUnitRecord::query()
            ->where('type', 'dormitory')
            ->where('status', 'active')
            ->when($search !== null && trim($search) !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('location_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit($safeLimit)
            ->get(['id', 'code', 'name', 'location_name'])
            ->map(static fn (OrganizationUnitRecord $record): DormitoryUnitOptionData => new DormitoryUnitOptionData(
                id: (string) $record->getKey(),
                code: (string) $record->code,
                name: (string) $record->name,
                locationName: $record->location_name === null ? null : (string) $record->location_name,
            ))
            ->values()
            ->all();
    }
}
