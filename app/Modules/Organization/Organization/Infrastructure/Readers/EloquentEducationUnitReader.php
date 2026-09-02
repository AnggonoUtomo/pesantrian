<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Infrastructure\Readers;

use App\Modules\Organization\Organization\Application\Contracts\EducationUnitReader;
use App\Modules\Organization\Organization\Application\DTO\EducationUnitOptionData;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;

final class EloquentEducationUnitReader implements EducationUnitReader
{
    public function options(?string $search = null, int $limit = 50): array
    {
        $safeLimit = max(1, min($limit, 100));

        return OrganizationUnitRecord::query()
            ->where('type', 'education_unit')
            ->where('status', 'active')
            ->when($search !== null && trim($search) !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit($safeLimit)
            ->get(['id', 'code', 'name'])
            ->map(static fn (OrganizationUnitRecord $record): EducationUnitOptionData => new EducationUnitOptionData(
                id: (string) $record->getKey(),
                code: (string) $record->code,
                name: (string) $record->name,
            ))
            ->values()
            ->all();
    }
}
