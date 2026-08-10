<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ChangeUserStatus
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(?Authenticatable $actor, string $userId, UserStatus $status): void
    {
        $this->authorization->ensure($actor, 'user.status.manage');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected || $user->deletedAt !== null) {
            throw new ProtectedUserMutation;
        }

        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.status_changed',
            subjectType: 'user',
            mutation: fn (): UserData => $this->repository->changeStatus($userId, $status),
            subjectId: static fn (UserData $updatedUser): string => $updatedUser->id,
            metadata: static fn (UserData $updatedUser): array => [
                'from_status' => $user->status->value,
                'to_status' => $status->value,
                'changed_fields' => ['status'],
            ],
        );
    }
}
