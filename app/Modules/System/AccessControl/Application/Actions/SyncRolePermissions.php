<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class SyncRolePermissions
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
    ) {}

    /** @param array<int, mixed> $permissions */
    public function execute(?Authenticatable $actor, Role $role, array $permissions): void
    {
        $this->authorization->ensurePermissionsCanBeAssigned($actor, $role);
        if (array_filter($permissions, static fn ($permission): bool => ! is_string($permission)) !== []) {
            throw new InvalidArgumentException('Permission harus berupa string.');
        }

        $permissions = array_unique($permissions);
        $valid = Permission::query()->where('guard_name', 'web')->whereIn('name', $permissions)->pluck('name')->all();
        if (count($valid) !== count($permissions)) {
            throw new InvalidArgumentException('Permission tidak valid.');
        }
        $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.permissions_synced',
            subjectType: 'role',
            mutation: static function () use ($role, $permissions): Role {
                $role->syncPermissions($permissions);

                return $role;
            },
            subjectId: static fn (Role $updatedRole): string => (string) $updatedRole->getKey(),
            metadata: static fn (Role $updatedRole): array => [
                'role_name' => $updatedRole->name,
                'permission_keys' => $permissions,
                'permission_count' => count($permissions),
            ],
        );
    }
}
