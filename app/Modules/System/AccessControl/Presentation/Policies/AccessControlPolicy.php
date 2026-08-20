<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Policies;

use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;

final class AccessControlPolicy
{
    public function __construct(private readonly AuthorizeRoleMutation $authorization) {}

    public function viewAny(?Authenticatable $actor): bool
    {
        return $this->authorization->canViewAccessControl($actor);
    }

    public function create(?Authenticatable $actor): bool
    {
        return $this->authorization->canManage($actor);
    }

    public function update(?Authenticatable $actor, Role $role): bool
    {
        return $this->authorization->canAssignPermissions($actor, $role->name);
    }

    public function delete(?Authenticatable $actor, Role $role): bool
    {
        return $this->authorization->canMutateRole($actor, $role->name);
    }
}
