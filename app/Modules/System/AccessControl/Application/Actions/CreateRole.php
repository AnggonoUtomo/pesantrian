<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Contracts\RoleRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Domain\Exceptions\DuplicateRole;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class CreateRole
{
    public function __construct(
        private AuthorizeRoleMutation $authorization,
        private AccessControlActivityPublisher $activities,
        private RoleRepository $roles,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        string $name,
        ?string $correlationId = null,
    ): RoleData {
        $this->authorization->ensureAllowed($actor);

        $name = trim($name);

        if ($name === '' || strlen($name) < 2 || strlen($name) > 100 || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 _-]*$/', $name)) {
            throw new InvalidArgumentException('Nama role tidak valid.');
        }

        if ($this->roles->existsByName($name, 'web')) {
            throw new DuplicateRole;
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'access_control.role.created',
            subjectType: 'role',
            mutation: fn (): RoleData => $this->roles->create($name, 'web'),
            subjectId: static fn (RoleData $role): string => $role->id,
            metadata: static fn (RoleData $role): array => ['role_name' => $role->name],
            correlationId: $correlationId,
        );
    }
}
