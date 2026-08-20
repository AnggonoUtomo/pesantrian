<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories;

use App\Modules\System\AccessControl\Application\Contracts\PermissionCatalog;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;

final class EloquentPermissionCatalog implements PermissionCatalog
{
    public function existingNames(array $names, string $guardName): array
    {
        $values = Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $existing = [];

        foreach ($values as $value) {
            if (is_string($value)) {
                $existing[] = $value;
            }
        }

        return $existing;
    }
}
