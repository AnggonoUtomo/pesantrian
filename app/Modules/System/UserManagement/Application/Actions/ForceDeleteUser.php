<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ForceDeleteUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(?Authenticatable $actor, string $userId): void
    {
        $this->authorization->ensure($actor, 'user.force.delete');
        $user = $this->repository->find($userId);

        if ($user === null || $user->isProtected || $user->deletedAt === null) {
            throw new ProtectedUserMutation;
        }

        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.force_deleted',
            subjectType: 'user',
            mutation: function () use ($userId): string {
                $this->repository->forceDelete($userId);

                return $userId;
            },
            subjectId: static fn (string $deletedUserId): string => $deletedUserId,
            metadata: static fn (string $deletedUserId): array => ['changed_fields' => ['deleted_at']],
        );
    }
}
