<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ImpersonationSession
{
    public function start(
        Authenticatable $actor,
        string $targetUserId,
        string $reason,
        ?string $correlationId = null,
    ): void;

    public function leave(Authenticatable $actor): void;

    public function active(): bool;
}
