<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\DTO\AuthorizationDecision;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

final class SpatieAuthorizationAdapter implements AuthorizationCapability
{
    public function can(?Authenticatable $actor, string $permission): AuthorizationDecision
    {
        if ($actor === null || ! method_exists($actor, 'hasPermissionTo')) {
            return AuthorizationDecision::deny('unauthenticated');
        }

        try {
            return $this->isSuperSystem($actor) || $actor->hasPermissionTo($permission)
                ? AuthorizationDecision::allow('permission_granted')
                : AuthorizationDecision::deny('permission_missing');
        } catch (PermissionDoesNotExist) {
            return AuthorizationDecision::deny('permission_unknown');
        }
    }

    public function canAny(?Authenticatable $actor, array $permissions): AuthorizationDecision
    {
        if ($actor === null || ! method_exists($actor, 'hasAnyPermission')) {
            return AuthorizationDecision::deny('unauthenticated');
        }

        try {
            return $this->isSuperSystem($actor) || $actor->hasAnyPermission($permissions)
                ? AuthorizationDecision::allow('permission_granted')
                : AuthorizationDecision::deny('permission_missing');
        } catch (PermissionDoesNotExist) {
            return AuthorizationDecision::deny('permission_unknown');
        }
    }

    public function hasRole(?Authenticatable $actor, string $role): AuthorizationDecision
    {
        if ($actor === null || ! method_exists($actor, 'hasRole')) {
            return AuthorizationDecision::deny('unauthenticated');
        }

        return $actor->hasRole($role)
            ? AuthorizationDecision::allow('role_granted')
            : AuthorizationDecision::deny('role_missing');
    }

    public function isSuperSystem(?Authenticatable $actor): bool
    {
        return $actor !== null
            && method_exists($actor, 'hasRole')
            && $actor->hasRole('SuperSystem');
    }
}
