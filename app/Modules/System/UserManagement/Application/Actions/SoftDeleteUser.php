<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\Exceptions\SelfUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SoftDeleteUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $userId,
        ?string $reason = null,
        ?string $correlationId = null,
    ): void {
        $this->authorization->ensure($actor, 'user.delete');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected || $user->deletedAt !== null) {
            throw new ProtectedUserMutation;
        }

        if ($actor !== null && (string) $actor->getAuthIdentifier() === $userId) {
            throw new SelfUserMutation;
        }

        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.deleted',
            subjectType: 'user',
            mutation: function () use ($userId): string {
                $this->repository->softDelete($userId);

                return $userId;
            },
            subjectId: static fn (string $deletedUserId): string => $deletedUserId,
            metadata: static fn (string $deletedUserId): array => ['changed_fields' => ['deleted_at']],
            reason: $reason,
            correlationId: $correlationId,
        );
    }
}
