<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class CreateRole
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
    ) {}

    public function execute(?Authenticatable $actor, string $name): Role
    {
        $this->authorization->ensureAllowed($actor);

        $name = trim($name);

        if ($name === '' || strlen($name) < 2 || strlen($name) > 100 || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 _-]*$/', $name)) {
            throw new InvalidArgumentException('Nama role tidak valid.');
        }

        if (Role::query()->where('name', $name)->where('guard_name', 'web')->exists()) {
            throw new InvalidArgumentException('Role sudah tersedia.');
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.created',
            subjectType: 'role',
            mutation: static fn (): Role => Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]),
            subjectId: static fn (Role $role): string => (string) $role->getKey(),
            metadata: static fn (Role $role): array => ['role_name' => $role->name],
        );
    }
}
