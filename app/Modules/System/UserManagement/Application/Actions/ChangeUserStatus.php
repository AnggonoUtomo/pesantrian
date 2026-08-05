<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ChangeUserStatus
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $userId, UserStatus $status): void
    {
        $this->authorization->ensure($actor, 'user.status.manage');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected) {
            throw new ProtectedUserMutation;
        }

        $this->repository->changeStatus($userId, $status);
    }
}
