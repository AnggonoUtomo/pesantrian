<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\DirectPermissionAssignmentCapability;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use LogicException;

final readonly class MutateUserPermission
{
    public function __construct(
        private UserRepository $users,
        private DirectPermissionAssignmentCapability $permissions,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function assign(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionName,
        ?string $correlationId = null,
    ): UserData {
        $this->ensureMutable($target);
        $permissionName = trim($permissionName);

        return $this->mutate(
            $actor,
            $target,
            'user.permission_assigned',
            function () use ($actor, $target, $permissionName): string {
                $this->permissions->assignPermission($actor, $target, $permissionName);

                return $permissionName;
            },
            $correlationId,
        );
    }

    public function revoke(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionId,
        ?string $correlationId = null,
    ): UserData {
        $this->ensureMutable($target);

        return $this->mutate(
            $actor,
            $target,
            'user.permission_revoked',
            fn (): string => $this->permissions->revokePermission($actor, $target, $permissionId),
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

    /** @param Closure(): string $mutation */
    private function mutate(
        Authenticatable $actor,
        Authenticatable $target,
        string $action,
        Closure $mutation,
        ?string $correlationId,
    ): UserData {
        $targetId = (string) $target->getAuthIdentifier();

        $this->activities->publish(
            actorId: (string) $actor->getAuthIdentifier(),
            action: $action,
            subjectType: 'user',
            mutation: function () use ($mutation, $targetId): array {
                return [
                    'target_id' => $targetId,
                    'permission_name' => $mutation(),
                ];
            },
            subjectId: static fn (array $result): string => $result['target_id'],
            metadata: static fn (array $result): array => [
                'permission_name' => $result['permission_name'],
            ],
            correlationId: $correlationId,
        );

        return $this->users->find($targetId)
            ?? throw new LogicException('User hilang setelah mutation direct permission.');
    }
}
