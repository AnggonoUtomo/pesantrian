<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\Contracts\DirectPermissionAssignmentCapability;
use App\Modules\System\AccessControl\Application\Contracts\Exceptions\PermissionNotFound;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class SpatieDirectPermissionAssignmentAdapter implements DirectPermissionAssignmentCapability
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function assignPermission(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionName,
    ): void {
        $this->ensureAllowed($actor);

        if (! method_exists($target, 'givePermissionTo')) {
            throw new InvalidArgumentException('Target tidak mendukung direct permission.');
        }

        $target->givePermissionTo($this->findByName($permissionName));
    }

    public function revokePermission(
        Authenticatable $actor,
        Authenticatable $target,
        string $permissionId,
    ): string {
        $this->ensureAllowed($actor);

        if (! method_exists($target, 'revokePermissionTo')) {
            throw new InvalidArgumentException('Target tidak mendukung pencabutan direct permission.');
        }

        $permission = $this->findById($permissionId);
        $target->revokePermissionTo($permission);

        return $permission->name;
    }

    private function ensureAllowed(Authenticatable $actor): void
    {
        if (! $this->authorization->can($actor, 'access_control.permission.assign')->allowed) {
            throw new AuthorizationException('Assignment direct permission tidak diizinkan.');
        }
    }

    private function findByName(string $permissionName): Permission
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', trim($permissionName))
            ->first();

        return $permission instanceof Permission ? $permission : throw new PermissionNotFound;
    }

    private function findById(string $permissionId): Permission
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->find($permissionId);

        return $permission instanceof Permission ? $permission : throw new PermissionNotFound;
    }
}
