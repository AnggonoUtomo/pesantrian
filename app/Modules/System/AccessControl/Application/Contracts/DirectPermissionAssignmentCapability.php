<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface DirectPermissionAssignmentCapability
{
    public function assignPermission(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionName,
    ): void;

    public function revokePermission(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionId,
    ): string;
}
