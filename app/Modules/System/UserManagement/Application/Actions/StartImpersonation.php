<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\ImpersonationRequestData;
use App\Modules\System\UserManagement\Application\DTO\ImpersonationStateData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ImpersonationTargetForbidden;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class StartImpersonation
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private ImpersonationSession $session,
    ) {}

    public function execute(Authenticatable $actor, ImpersonationRequestData $request): ImpersonationStateData
    {
        $this->authorization->ensure($actor, 'user.impersonate');
        $target = $this->repository->find($request->targetUserId);

        if ($target === null
            || $target->id === (string) $actor->getAuthIdentifier()
            || $target->isProtected
            || $target->deletedAt !== null
            || $target->status->value !== 'active') {
            throw new ImpersonationTargetForbidden;
        }

        if ($request->correlationId === null) {
            $this->session->start($actor, $target->id, $request->reason);
        } else {
            $this->session->start($actor, $target->id, $request->reason, $request->correlationId);
        }

        $actorName = data_get($actor, 'name');

        return new ImpersonationStateData(
            active: true,
            actorName: is_string($actorName) && $actorName !== '' ? $actorName : 'Actor',
            targetName: $target->name,
        );
    }
}
