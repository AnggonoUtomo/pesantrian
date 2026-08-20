<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Domain\Exceptions\ImpersonationNotActive;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class EndImpersonation
{
    public function __construct(private ImpersonationSession $session) {}

    public function execute(Authenticatable $actor): void
    {
        if (! $this->session->active()) {
            throw new ImpersonationNotActive;
        }

        $this->session->leave($actor);
    }
}
