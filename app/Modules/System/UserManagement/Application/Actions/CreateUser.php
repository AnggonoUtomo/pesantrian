<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, CreateUserData $data): UserData
    {
        $this->authorization->ensure($actor, 'user.create');

        return $this->repository->create($data);
    }
}
