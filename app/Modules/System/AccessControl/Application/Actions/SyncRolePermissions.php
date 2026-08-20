<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Contracts\PermissionCatalog;
use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class SyncRolePermissions
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
        private RoleRepository $roles,
        private PermissionCatalog $permissionCatalog,
    ) {}

    /** @param array<int, mixed> $permissions */
    public function execute(
        ?Authenticatable $actor,
        string $roleId,
        array $permissions,
        ?string $correlationId = null,
    ): RoleData {
        $role = $this->roles->find($roleId)
            ?? throw new InvalidArgumentException('Role tidak ditemukan.');
        $this->authorization->ensurePermissionsCanBeAssigned($actor, $role->name);
        if (array_filter($permissions, static fn ($permission): bool => ! is_string($permission)) !== []) {
            throw new InvalidArgumentException('Permission harus berupa string.');
        }

        $permissions = array_values(array_unique($permissions));
        $valid = $this->permissionCatalog->existingNames($permissions, 'web');
        if (count($valid) !== count($permissions)) {
            throw new InvalidArgumentException('Permission tidak valid.');
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.permissions_synced',
            subjectType: 'role',
            mutation: fn (): RoleData => $this->roles->syncPermissions($roleId, $permissions),
            subjectId: static fn (RoleData $updatedRole): string => $updatedRole->id,
            metadata: static fn (RoleData $updatedRole): array => [
                'role_name' => $updatedRole->name,
                'permission_keys' => $permissions,
                'permission_count' => count($permissions),
            ],
            correlationId: $correlationId,
        );
    }
}
