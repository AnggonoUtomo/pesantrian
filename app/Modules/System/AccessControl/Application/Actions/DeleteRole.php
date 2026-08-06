<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class DeleteRole
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
    ) {}

    public function execute(?Authenticatable $actor, Role $role): void
    {
        $this->authorization->ensureRoleCanBeMutated($actor, $role);
        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.deleted',
            subjectType: 'role',
            mutation: static function () use ($role): Role {
                $role->delete();

                return $role;
            },
            subjectId: static fn (Role $deletedRole): string => (string) $deletedRole->getKey(),
            metadata: static fn (Role $deletedRole): array => ['role_name' => $deletedRole->name],
        );
    }
}
