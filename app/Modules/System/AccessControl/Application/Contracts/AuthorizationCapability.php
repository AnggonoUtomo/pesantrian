<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use App\Modules\System\AccessControl\Application\DTO\AuthorizationDecision;
use Illuminate\Contracts\Auth\Authenticatable;

interface AuthorizationCapability
{
    public function can(?Authenticatable $actor, string $permission): AuthorizationDecision;

    /** @param list<string> $permissions */
    public function canAny(?Authenticatable $actor, array $permissions): AuthorizationDecision;

    public function hasRole(?Authenticatable $actor, string $role): AuthorizationDecision;

    public function isSuperSystem(?Authenticatable $actor): bool;
}
