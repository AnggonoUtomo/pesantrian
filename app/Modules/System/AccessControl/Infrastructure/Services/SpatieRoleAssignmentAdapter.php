<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Services;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class SpatieRoleAssignmentAdapter implements RoleAssignmentCapability
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function assignRole(Authenticatable $actor, Authenticatable $target, string $role): void
    {
        $this->ensureAllowed($actor, $role);

        if (! method_exists($target, 'assignRole')) {
            throw new InvalidArgumentException('Target tidak mendukung assignment role.');
        }

        $target->assignRole($this->resolveRole($role));
    }

    public function revokeRole(Authenticatable $actor, Authenticatable $target, string $role): void
    {
        $this->ensureAllowed($actor, $role);

        if (! method_exists($target, 'removeRole')) {
            throw new InvalidArgumentException('Target tidak mendukung pencabutan role.');
        }

        $target->removeRole($this->resolveRole($role));
    }

    public function syncRoles(Authenticatable $actor, Authenticatable $target, array $roles): void
    {
        if (! method_exists($target, 'syncRoles')) {
            throw new InvalidArgumentException('Target tidak mendukung pengaturan role.');
        }

        $roles = array_values(array_unique(array_map(static fn (string $role): string => trim($role), $roles)));

        if ($roles === [] || in_array('', $roles, true)) {
            throw new InvalidArgumentException('Minimal satu role wajib dipilih.');
        }

        foreach ($roles as $role) {
            $this->ensureAllowed($actor, $role);
        }

        $target->syncRoles(array_map($this->resolveRole(...), $roles));
    }

    private function ensureAllowed(Authenticatable $actor, string $role): void
    {
        $role = trim($role);

        if ($role === '') {
            throw new InvalidArgumentException('Role wajib diisi.');
        }

        if (! $this->authorization->can($actor, 'access_control.role.assign')->allowed) {
            throw new AuthorizationException('Assignment role tidak diizinkan.');
        }

        if ($role === 'SuperSystem' && ! $this->authorization->isSuperSystem($actor)) {
            throw new AuthorizationException('Role SuperSystem hanya dapat dikelola oleh SuperSystem.');
        }
    }

    private function resolveRole(string $role): Role
    {
        $resolved = Role::query()
            ->where('name', trim($role))
            ->where('guard_name', 'web')
            ->first();

        if (! $resolved instanceof Role) {
            throw new InvalidArgumentException("Role [$role] tidak ditemukan.");
        }

        return $resolved;
    }
}
