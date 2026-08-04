<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Policies;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;

final class AccessControlPolicy
{
    public function __construct(private readonly AuthorizationCapability $authorization) {}

    public function viewAny(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'access_control.role.manage')->allowed;
    }

    public function create(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'access_control.role.manage')->allowed;
    }

    public function update(?Authenticatable $actor, Role $role): bool
    {
        return $this->authorization->can($actor, 'access_control.role.manage')->allowed
            && $role->name !== 'SuperSystem';
    }

    public function delete(?Authenticatable $actor, Role $role): bool
    {
        return $this->authorization->can($actor, 'access_control.role.manage')->allowed
            && $role->name !== 'SuperSystem';
    }
}
