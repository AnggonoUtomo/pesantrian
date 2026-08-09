<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\BulkUserLifecycleResult;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

final readonly class BulkUserLifecycle
{
    public function __construct(
        private AuthorizeUserAction $authorization,
        private UserRepository $repository,
        private UserManagementActivityPublisher $activities,
    ) {}

    /** @param list<string> $userIds */
    public function archive(?Authenticatable $actor, array $userIds): BulkUserLifecycleResult
    {
        return $this->execute(
            actor: $actor,
            userIds: $userIds,
            permission: 'user.delete',
            action: 'user.deleted',
            invalidMessage: 'Operasi dibatalkan. Semua user terpilih harus masih aktif dan bukan SuperSystem.',
            isValid: static fn (UserData $user): bool => ! $user->isProtected && $user->deletedAt === null,
            mutate: function (string $userId): void {
                $this->repository->softDelete($userId);
            },
        );
    }

    /** @param list<string> $userIds */
    public function forceDelete(?Authenticatable $actor, array $userIds): BulkUserLifecycleResult
    {
        return $this->execute(
            actor: $actor,
            userIds: $userIds,
            permission: 'user.force.delete',
            action: 'user.force_deleted',
            invalidMessage: 'Operasi dibatalkan. Semua user terpilih harus sudah diarsipkan dan bukan SuperSystem.',
            isValid: static fn (UserData $user): bool => ! $user->isProtected && $user->deletedAt !== null,
            mutate: function (string $userId): void {
                $this->repository->forceDelete($userId);
            },
        );
    }

    /**
     * @param  list<string>  $userIds
     * @param  \Closure(UserData): bool  $isValid
     * @param  \Closure(string): void  $mutate
     */
    private function execute(
        ?Authenticatable $actor,
        array $userIds,
        string $permission,
        string $action,
        string $invalidMessage,
        \Closure $isValid,
        \Closure $mutate,
    ): BulkUserLifecycleResult {
        $this->authorization->ensure($actor, $permission);

        foreach ($userIds as $userId) {
            $user = $this->repository->find($userId);

            if ($user === null || ! $isValid($user)) {
                return BulkUserLifecycleResult::rejected($invalidMessage);
            }
        }

        $correlationId = (string) Str::ulid();
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        $this->repository->transaction(function () use (
            $userIds,
            $actorId,
            $action,
            $correlationId,
            $mutate,
        ): void {
            foreach ($userIds as $userId) {
                $this->activities->publish(
                    actorId: $actorId,
                    action: $action,
                    subjectType: 'user',
                    mutation: function () use ($mutate, $userId): string {
                        $mutate($userId);

                        return $userId;
                    },
                    subjectId: static fn (string $mutatedUserId): string => $mutatedUserId,
                    metadata: static fn (string $mutatedUserId): array => ['changed_fields' => ['deleted_at']],
                    correlationId: $correlationId,
                );
            }
        });

        return BulkUserLifecycleResult::completed(count($userIds));
    }
}
