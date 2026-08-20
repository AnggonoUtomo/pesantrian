<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Models\User;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateUser
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private RoleAssignmentCapability $roles,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(
        ?Authenticatable $actor,
        CreateUserData $data,
        ?string $correlationId = null,
    ): UserData {
        $this->authorization->ensure($actor, 'user.create');
        if ($data->status->value !== 'active') {
            $this->authorization->ensure($actor, 'user.status.manage');
        }

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.created',
            subjectType: 'user',
            mutation: function () use ($actor, $data): UserData {
                $user = $this->repository->create($data);

                if ($data->role !== null && $actor !== null) {
                    $target = User::query()->findOrFail($user->id);
                    $this->roles->assignRole($actor, $target, $data->role);
                }

                return $user;
            },
            subjectId: static fn (UserData $user): string => $user->id,
            metadata: static fn (UserData $user): array => [
                'changed_fields' => ['name', 'email', 'status', 'role'],
            ],
            correlationId: $correlationId,
        );
    }
}
