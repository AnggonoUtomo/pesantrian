<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final class AuthorizeRoleMutation
{
    public function __construct(private readonly AuthorizationCapability $authorization) {}

    public function ensureAllowed(?Authenticatable $actor): void
    {
        $decision = $this->authorization->can($actor, 'access_control.role.manage');

        if (! $decision->allowed) {
            throw new AuthorizationException('Role mutation tidak diizinkan.');
        }
    }
}
