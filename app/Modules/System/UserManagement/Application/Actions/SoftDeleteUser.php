<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SoftDeleteUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $userId): void
    {
        $this->authorization->ensure($actor, 'user.delete');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected) {
            throw new ProtectedUserMutation;
        }

        $this->repository->softDelete($userId);
    }
}
