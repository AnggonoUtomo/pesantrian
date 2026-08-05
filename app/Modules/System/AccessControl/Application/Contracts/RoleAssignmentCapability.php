<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface RoleAssignmentCapability
{
    /**
     * Assign a role to a target actor after authorization and policy checks.
     */
    public function assignRole(Authenticatable $actor, Authenticatable $target, string $role): void;

    /**
     * Revoke a role from a target actor after authorization and policy checks.
     */
    public function revokeRole(Authenticatable $actor, Authenticatable $target, string $role): void;
}
