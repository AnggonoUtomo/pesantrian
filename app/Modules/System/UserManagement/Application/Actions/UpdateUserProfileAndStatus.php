<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateUserProfileAndStatus
{
    public function __construct(
        private UserRepository $repository,
        private UpdateUser $updateUser,
        private ChangeUserStatus $changeUserStatus,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $userId,
        ?UpdateUserData $profile,
        ?UserStatus $status,
        ?string $correlationId = null,
    ): UserData {
        return $this->repository->transaction(function () use (
            $actor,
            $userId,
            $profile,
            $status,
            $correlationId,
        ): UserData {
            $updated = $this->repository->find($userId);

            if ($updated === null) {
                throw new ProtectedUserMutation;
            }

            if ($profile !== null) {
                $updated = $this->updateUser->execute(
                    $actor,
                    $userId,
                    $profile,
                    $correlationId,
                );
            }

            if ($status !== null) {
                $updated = $this->changeUserStatus->execute(
                    $actor,
                    $userId,
                    $status,
                    $correlationId,
                );
            }

            return $updated;
        });
    }
}
