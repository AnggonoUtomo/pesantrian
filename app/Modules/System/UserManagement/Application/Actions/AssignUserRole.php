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

    /** @param list<string> $roles */
    public function execute(Authenticatable $actor, Authenticatable $target, array $roles): void
    {
        $this->authorization->ensure($actor, 'user.update');
        $targetId = (string) $target->getAuthIdentifier();
        $user = $this->repository->find($targetId);

        if ($user === null || $user->isProtected || $user->deletedAt !== null) {
            throw new ProtectedUserMutation;
        }

        $roles = array_values(array_unique(array_map(static fn (string $role): string => trim($role), $roles)));

        if ($roles === [] || in_array('', $roles, true)) {
            throw new InvalidArgumentException('Minimal satu role wajib dipilih.');
        }

        $this->activities->publish(
            actorId: (string) $actor->getAuthIdentifier(),
            action: 'user.role_assigned',
            subjectType: 'user',
            mutation: function () use ($actor, $target, $roles, $targetId): string {
                $this->roles->syncRoles($actor, $target, $roles);

                return $targetId;
            },
            subjectId: static fn (string $targetId): string => $targetId,
            metadata: static fn (string $targetId): array => ['role_names' => $roles],
        );
    }
}
