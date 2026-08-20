<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class DeleteRole
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
        private RoleRepository $roles,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $roleId,
        ?string $correlationId = null,
    ): void {
        $role = $this->roles->find($roleId)
            ?? throw new InvalidArgumentException('Role tidak ditemukan.');
        $this->authorization->ensureRoleCanBeMutated($actor, $role->name);
        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.deleted',
            subjectType: 'role',
            mutation: fn (): RoleData => $this->roles->delete($roleId),
            subjectId: static fn (RoleData $deletedRole): string => $deletedRole->id,
            metadata: static fn (RoleData $deletedRole): array => ['role_name' => $deletedRole->name],
            correlationId: $correlationId,
        );
    }
}
