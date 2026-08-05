<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $userId, UpdateUserData $data): UserData
    {
        $this->authorization->ensure($actor, 'user.update');

        return $this->repository->update($userId, $data);
    }
}
