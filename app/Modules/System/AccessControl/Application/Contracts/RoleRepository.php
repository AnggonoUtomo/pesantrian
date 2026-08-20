<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use App\Modules\System\AccessControl\Application\DTO\RoleData;
use Closure;

interface RoleRepository
{
    public function existsByName(string $name, string $guardName): bool;

    public function find(string $id): ?RoleData;

    public function create(string $name, string $guardName): RoleData;

    public function rename(string $id, string $name): RoleData;

    public function delete(string $id): RoleData;

    /** @param list<string> $permissions */
    public function syncPermissions(string $id, array $permissions): RoleData;

    /**
     * @template TResult
     *
     * @param  Closure(): TResult  $callback
     * @return TResult
     */
    public function transaction(Closure $callback): mixed;
}
