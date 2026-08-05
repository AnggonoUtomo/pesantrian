<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class AssignUserRole
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private RoleAssignmentCapability $roles,
    ) {}

    public function execute(Authenticatable $actor, Authenticatable $target, string $role): void
    {
        $this->authorization->ensure($actor, 'user.update');
        $role = trim($role);

        if ($role === '') {
            throw new InvalidArgumentException('Role wajib diisi.');
        }

        $this->roles->assignRole($actor, $target, $role);
    }
}
