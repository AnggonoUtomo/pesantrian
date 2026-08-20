<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Persistence\Repositories;

use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Domain\Exceptions\DuplicateRole;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Closure;
use Illuminate\Database\QueryException;

final class EloquentRoleRepository implements RoleRepository
{
    public function existsByName(string $name, string $guardName): bool
    {
        return Role::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->exists();
    }

    public function find(string $id): ?RoleData
    {
        $role = Role::query()->with('permissions:id,name')->find($id);

        return $role instanceof Role ? $this->map($role) : null;
    }

    public function create(string $name, string $guardName): RoleData
    {
        try {
            $role = Role::query()->create([
                'name' => $name,
                'guard_name' => $guardName,
            ]);
        } catch (QueryException $exception) {
            $this->throwDuplicate($exception);
        }

        return $this->map($role);
    }

    public function rename(string $id, string $name): RoleData
    {
        $role = Role::query()->with('permissions:id,name')->findOrFail($id);

        try {
            $role->update(['name' => $name]);
        } catch (QueryException $exception) {
            $this->throwDuplicate($exception);
        }

        return $this->map($role->refresh());
    }

    public function delete(string $id): RoleData
    {
        $role = Role::query()->with('permissions:id,name')->findOrFail($id);
        $data = $this->map($role);
        $role->delete();

        return $data;
    }

    public function syncPermissions(string $id, array $permissions): RoleData
    {
        $role = Role::query()->findOrFail($id);
        $role->syncPermissions($permissions);
        $role->load('permissions:id,name');

        return $this->map($role);
    }

    public function transaction(Closure $callback): mixed
    {
        return Role::query()->getConnection()->transaction($callback);
    }

    private function map(Role $role): RoleData
    {
        $permissions = [];

        foreach ($role->permissions as $permission) {
            $permissions[] = $permission->name;
        }

        return new RoleData(
            id: (string) $role->getKey(),
            name: $role->name,
            guardName: $role->guard_name,
            permissions: $permissions,
            isProtected: $role->name === 'SuperSystem',
        );
    }

    private function throwDuplicate(QueryException $exception): never
    {
        if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
            throw new DuplicateRole(previous: $exception);
        }

        throw $exception;
    }
}
