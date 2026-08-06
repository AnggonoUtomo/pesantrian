<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

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
        private UserManagementActivityPublisher $activities,
    ) {}

    public function execute(?Authenticatable $actor, CreateUserData $data): UserData
    {
        $this->authorization->ensure($actor, 'user.create');

        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'user.created',
            subjectType: 'user',
            mutation: fn (): UserData => $this->repository->create($data),
            subjectId: static fn (UserData $user): string => $user->id,
            metadata: static fn (UserData $user): array => [
                'changed_fields' => ['name', 'email', 'status'],
            ],
        );
    }
}
