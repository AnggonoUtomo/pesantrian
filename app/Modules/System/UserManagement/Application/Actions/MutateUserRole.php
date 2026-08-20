<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\Exceptions\RoleNotFound;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;
use LogicException;

final readonly class MutateUserRole
{
    public function __construct(
        private UserRepository $users,
        private RoleCatalogCapability $roleCatalog,
        private RoleAssignmentCapability $roles,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function assign(
        Authenticatable $actor,
        Authenticatable $target,
        string $roleName,
        ?string $correlationId = null,
    ): UserData {
        $this->ensureMutable($target);

        return $this->mutate(
            $actor,
            $target,
            trim($roleName),
            'user.role_assigned',
            function () use ($actor, $target, $roleName): void {
                $this->roles->assignRole($actor, $target, $roleName);
            },
            $correlationId,
        );
    }

    public function revoke(
        Authenticatable $actor,
        Authenticatable $target,
        string $roleId,
        ?string $correlationId = null,
    ): UserData {
        $this->ensureMutable($target);
        $role = $this->roleCatalog->findRole($roleId) ?? throw new RoleNotFound;

        return $this->mutate(
            $actor,
            $target,
            $role->name,
            'user.role_revoked',
            function () use ($actor, $target, $role): void {
                $this->roles->revokeRole($actor, $target, $role->name);
            },
            $correlationId,
        );
    }

    private function ensureMutable(Authenticatable $target): void
    {
        $targetId = (string) $target->getAuthIdentifier();
        $user = $this->users->find($targetId);

        if ($user === null || $user->isProtected || $user->deletedAt !== null) {
            throw new ProtectedUserMutation;
        }
    }

    private function mutate(
        Authenticatable $actor,
        Authenticatable $target,
        string $roleName,
        string $action,
        callable $mutation,
        ?string $correlationId,
    ): UserData {
        $targetId = (string) $target->getAuthIdentifier();

        $this->activities->publish(
            actorId: (string) $actor->getAuthIdentifier(),
            action: $action,
            subjectType: 'user',
            mutation: function () use ($mutation, $targetId): string {
                $mutation();

                return $targetId;
            },
            subjectId: static fn (string $id): string => $id,
            metadata: static fn (string $id): array => ['role_name' => $roleName],
            correlationId: $correlationId,
        );

        return $this->users->find($targetId)
            ?? throw new LogicException('User hilang setelah mutation role.');
    }
}
