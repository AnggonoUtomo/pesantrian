<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\ImpersonationRequestData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class StartImpersonation
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private ImpersonationSession $session,
    ) {}

    public function execute(Authenticatable $actor, ImpersonationRequestData $request): void
    {
        $this->authorization->ensure($actor, 'user.impersonate');
        $target = $this->repository->find($request->targetUserId);

        if ($target === null || $target->isProtected) {
            throw new AuthorizationException('Target impersonation tidak diizinkan.');
        }

        $this->session->start($actor, $target->id, $request->reason);
    }
}
