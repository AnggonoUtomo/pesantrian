<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Policies;

use App\Models\User;
use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UserManagementPolicy
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function viewAny(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'user.view')->allowed;
    }

    public function view(?Authenticatable $actor, User $user): bool
    {
        return $this->authorization->can($actor, 'user.view')->allowed;
    }

    public function create(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'user.create')->allowed;
    }

    public function mutate(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'user.update')->allowed
            || $this->authorization->can($actor, 'user.status.manage')->allowed;
    }

    public function deleteAny(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'user.delete')->allowed;
    }

    public function update(?Authenticatable $actor, User $user): bool
    {
        return ! $user->isSuperSystem()
            && ! $user->trashed()
            && $this->authorization->can($actor, 'user.update')->allowed;
    }

    public function changeStatus(?Authenticatable $actor, User $user): bool
    {
        return ! $user->isSuperSystem()
            && $this->authorization->can($actor, 'user.status.manage')->allowed;
    }

    public function delete(?Authenticatable $actor, User $user): bool
    {
        return ! $user->isSuperSystem()
            && $this->authorization->can($actor, 'user.delete')->allowed;
    }

    public function restore(?Authenticatable $actor, User $user): bool
    {
        return $user->trashed()
            && ! $user->isSuperSystem()
            && $this->authorization->can($actor, 'user.restore')->allowed;
    }

    public function forceDelete(?Authenticatable $actor, User $user): bool
    {
        return $user->trashed()
            && ! $user->isSuperSystem()
            && $this->authorization->can($actor, 'user.force.delete')->allowed;
    }

    public function impersonate(?Authenticatable $actor, User $user): bool
    {
        return $user->canAuthenticate()
            && ! $user->isSuperSystem()
            && $this->authorization->can($actor, 'user.impersonate')->allowed;
    }
}
