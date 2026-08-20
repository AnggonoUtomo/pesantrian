<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $userId,
        UpdateUserData $data,
        ?string $correlationId = null,
    ): UserData {
        $this->authorization->ensure($actor, 'user.update');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected || $user->deletedAt !== null) {
            throw new ProtectedUserMutation;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.updated',
            subjectType: 'user',
            mutation: fn (): UserData => $this->repository->update($userId, $data),
            subjectId: static fn (UserData $user): string => $user->id,
            metadata: static fn (UserData $user): array => ['changed_fields' => ['name', 'email']],
            correlationId: $correlationId,
        );
    }
}
