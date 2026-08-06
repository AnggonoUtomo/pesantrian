<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class AssignUserRole
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private RoleAssignmentCapability $roles,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(Authenticatable $actor, Authenticatable $target, string $role): void
    {
        $this->authorization->ensure($actor, 'user.update');
        $targetId = (string) $target->getAuthIdentifier();
        $user = $this->repository->find($targetId);

        if ($user === null || $user->isProtected) {
            throw new ProtectedUserMutation;
        }

        $role = trim($role);

        if ($role === '') {
            throw new InvalidArgumentException('Role wajib diisi.');
        }

        $this->activities->publish(
            actorId: (string) $actor->getAuthIdentifier(),
            action: 'user.role_assigned',
            subjectType: 'user',
            mutation: function () use ($actor, $target, $role, $targetId): string {
                $this->roles->assignRole($actor, $target, $role);

                return $targetId;
            },
            subjectId: static fn (string $targetId): string => $targetId,
            metadata: static fn (string $targetId): array => ['role_name' => $role],
        );
    }
}
