<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;

final class SyncRolePermissions
{
    public function __construct(private readonly AuthorizeRoleMutation $authorization) {}

    /** @param array<int, string> $permissions */
    public function execute(?Authenticatable $actor, Role $role, array $permissions): void
    {
        $this->authorization->ensureRoleCanBeMutated($actor, $role);
        $role->syncPermissions($permissions);
    }
}
