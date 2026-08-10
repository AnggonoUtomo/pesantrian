<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Infrastructure\Persistence\Repositories;

use App\Models\User;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\PaginatedUserData;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Closure;

final class EloquentUserRepository implements UserRepository
{
    public function paginate(UserListFilter $filter): PaginatedUserData
    {
        $query = User::query()->with('roles:id,name');

        match ($filter->archive) {
            'active' => $query->withoutTrashed(),
            'archived' => $query->onlyTrashed(),
            default => $query->withTrashed(),
        };

        $users = $query
            ->when($filter->search !== null, static function ($query) use ($filter): void {
                $search = '%'.$filter->search.'%';
                $query->where(static function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($filter->status !== null, static function ($query) use ($filter): void {
                $query->where('status', $filter->status->value);
            })
            ->when($filter->role !== null, static function ($query) use ($filter): void {
                $query->whereHas('roles', static function ($query) use ($filter): void {
                    $query->where('name', $filter->role);
                });
            })
            ->orderBy('name')
            ->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedUserData(
            data: array_values($users->getCollection()->map(fn (User $user): UserData => $this->toData($user))->all()),
            total: $users->total(),
            currentPage: $users->currentPage(),
            lastPage: $users->lastPage(),
            perPage: $users->perPage(),
        );
    }

    public function find(string $userId): ?UserData
    {
        $user = User::query()->with('roles:id,name')->withTrashed()->find($userId);

        return $user === null ? null : $this->toData($user);
    }

    public function create(CreateUserData $data): UserData
    {
        $user = User::query()->create([
            'name' => trim($data->name),
            'email' => trim($data->email),
            'password' => $data->password,
            'status' => $data->status,
        ]);

        return $this->toData($user);
    }

    public function update(string $userId, UpdateUserData $data): UserData
    {
        $user = User::query()->withTrashed()->findOrFail($userId);
        $user->update([
            'name' => trim($data->name),
            'email' => trim($data->email),
        ]);

        return $this->toData($user->refresh());
    }

    public function changeStatus(string $userId, UserStatus $status): UserData
    {
        $user = User::query()->withTrashed()->findOrFail($userId);
        $user->update(['status' => $status]);

        return $this->toData($user->refresh());
    }

    public function softDelete(string $userId): void
    {
        User::query()->findOrFail($userId)->delete();
    }

    public function restore(string $userId): void
    {
        User::query()->withTrashed()->findOrFail($userId)->restore();
    }

    public function forceDelete(string $userId): void
    {
        User::query()->withTrashed()->findOrFail($userId)->forceDelete();
    }

    public function transaction(Closure $callback): mixed
    {
        return User::query()->getConnection()->transaction($callback);
    }

    private function toData(User $user): UserData
    {
        return new UserData(
            id: (string) $user->getKey(),
            name: $user->name,
            email: $user->email,
            status: $user->status,
            isProtected: $user->isSuperSystem(),
            deletedAt: $user->deleted_at?->toISOString(),
            roles: $this->roleNames($user),
            avatarUrl: $user->getFirstMedia('avatar') === null ? null : route('system.users.avatar', $user),
            emailVerified: $user->email_verified_at !== null,
            lastLoginAt: $user->last_login_at?->toISOString(),
        );
    }

    /** @return list<string> */
    private function roleNames(User $user): array
    {
        $roles = [];

        foreach ($user->getRoleNames() as $role) {
            $roles[] = (string) $role;
        }

        return $roles;
    }
}
