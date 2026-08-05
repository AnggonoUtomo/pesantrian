<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateRole
{
    public function __construct(private readonly AuthorizeRoleMutation $authorization) {}

    public function execute(?Authenticatable $actor, string $name): Role
    {
        $this->authorization->ensureAllowed($actor);

        $role = new Role;
        $role->name = trim($name);
        $role->guard_name = 'web';
        $role->save();

        return $role;
    }
}
