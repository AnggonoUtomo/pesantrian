<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final class AuthorizeRoleMutation
{
    public function __construct(private readonly AuthorizationCapability $authorization) {}

    public function ensureAllowed(?Authenticatable $actor): void
    {
        if (! $this->canManage($actor)) {
            throw new AuthorizationException('Role mutation tidak diizinkan.');
        }
    }

    public function canManage(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'access_control.role.manage')->allowed;
    }

    public function canViewAccessControl(?Authenticatable $actor): bool
    {
        return $this->canManage($actor)
            || $this->authorization->can($actor, 'access_control.permission.assign')->allowed;
    }

    public function canMutateRole(?Authenticatable $actor, Role $role): bool
    {
        return $this->canManage($actor) && $role->name !== 'SuperSystem';
    }

    public function canAssignPermissions(?Authenticatable $actor, Role $role): bool
    {
        return $this->authorization->can($actor, 'access_control.permission.assign')->allowed
            && $role->name !== 'SuperSystem';
    }

    public function ensureRoleCanBeMutated(?Authenticatable $actor, Role $role): void
    {
        if (! $this->canMutateRole($actor, $role)) {
            throw new AuthorizationException('Role mutation tidak diizinkan.');
        }
    }

    public function ensurePermissionsCanBeAssigned(?Authenticatable $actor, Role $role): void
    {
        if (! $this->canAssignPermissions($actor, $role)) {
            throw new AuthorizationException('Sinkronisasi permission tidak diizinkan.');
        }
    }
}
