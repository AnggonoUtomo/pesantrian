<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class UpdateRoleAndPermissions
{
    public function __construct(
        private RoleRepository $roles,
        private RenameRole $renameRole,
        private SyncRolePermissions $syncRolePermissions,
    ) {}

    /** @param list<string>|null $permissions */
    public function execute(
        ?Authenticatable $actor,
        string $roleId,
        ?string $name,
        ?array $permissions,
        ?string $correlationId = null,
    ): RoleData {
        return $this->roles->transaction(function () use (
            $actor,
            $roleId,
            $name,
            $permissions,
            $correlationId,
        ): RoleData {
            $updated = $this->roles->find($roleId)
                ?? throw new InvalidArgumentException('Role tidak ditemukan.');

            if ($name !== null) {
                $updated = $this->renameRole->execute($actor, $roleId, $name, $correlationId);
            }

            if ($permissions !== null) {
                $updated = $this->syncRolePermissions->execute(
                    $actor,
                    $roleId,
                    $permissions,
                    $correlationId,
                );
            }

            return $updated;
        });
    }
}
