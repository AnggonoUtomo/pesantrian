<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Queries;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserData;

final readonly class GetUser
{
    public function __construct(private UserRepository $repository) {}

    public function execute(string $userId): ?UserData
    {
        return $this->repository->find($userId);
    }
}
