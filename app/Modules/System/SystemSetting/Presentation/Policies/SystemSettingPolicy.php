<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Policies;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SystemSettingPolicy
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function viewAny(?Authenticatable $actor): bool
    {
        return $this->authorization->isSuperSystem($actor);
    }

    public function update(?Authenticatable $actor): bool
    {
        return $this->authorization->isSuperSystem($actor);
    }
}
