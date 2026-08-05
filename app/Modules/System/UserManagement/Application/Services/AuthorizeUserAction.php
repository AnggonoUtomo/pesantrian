<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class AuthorizeUserAction
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function ensure(?Authenticatable $actor, string $permission): void
    {
        $decision = $this->authorization->can($actor, $permission);

        if (! $decision->allowed) {
            throw new AuthorizationException('Aktor tidak memiliki izin untuk aksi user.');
        }
    }
}
