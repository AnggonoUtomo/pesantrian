<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Contracts;

use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;

interface UserRepository
{
    /** @return list<UserData> */
    public function list(UserListFilter $filter): array;

    public function find(string $userId): ?UserData;

    public function create(CreateUserData $data): UserData;

    public function update(string $userId, UpdateUserData $data): UserData;

    public function changeStatus(string $userId, UserStatus $status): UserData;

    public function softDelete(string $userId): void;

    public function restore(string $userId): void;

    public function forceDelete(string $userId): void;
}
