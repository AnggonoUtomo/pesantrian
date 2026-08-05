<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Controllers;

use App\Modules\System\UserManagement\Application\Actions\ChangeUserStatus;
use App\Modules\System\UserManagement\Application\Actions\CreateUser;
use App\Modules\System\UserManagement\Application\Actions\SoftDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\UpdateUser;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Application\Queries\GetUser;
use App\Modules\System\UserManagement\Application\Queries\ListUsers;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use App\Modules\System\UserManagement\Presentation\Requests\ChangeUserStatusRequest;
use App\Modules\System\UserManagement\Presentation\Requests\StoreUserRequest;
use App\Modules\System\UserManagement\Presentation\Requests\UpdateUserRequest;
use App\Modules\System\UserManagement\Presentation\Resources\UserResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final class UserController implements HasMiddleware
{
    public function __construct(
        private readonly ListUsers $listUsers,
        private readonly GetUser $getUser,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly ChangeUserStatus $changeUserStatus,
        private readonly SoftDeleteUser $softDeleteUser,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:user.view', only: ['index', 'show']),
            new Middleware('can:user.create', only: ['store']),
            new Middleware('can:user.update', only: ['update']),
            new Middleware('can:user.status.manage', only: ['changeStatus']),
            new Middleware('can:user.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $filter = UserListFilter::from($request->string('search')->toString());
        $users = array_map(
            static fn ($user): array => (new UserResource($user))->toArray($request),
            $this->listUsers->execute($filter),
        );

        return Inertia::render('System/UserManagement/pages/Index', [
            'users' => $users,
            'filters' => ['search' => $filter->search],
        ]);
    }

    public function show(string $user): Response
    {
        abort_if(($data = $this->getUser->execute($user)) === null, 404);

        return Inertia::render('System/UserManagement/pages/Show', [
            'user' => (new UserResource($data))->resolve(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->createUser->execute($request->user(), new CreateUserData(
            name: (string) $data['name'],
            email: (string) $data['email'],
            password: (string) $data['password'],
        ));

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function update(UpdateUserRequest $request, string $user): RedirectResponse
    {
        $data = $request->validated();
        $this->updateUser->execute($request->user(), $user, new UpdateUserData(
            name: (string) $data['name'],
            email: (string) $data['email'],
        ));

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function changeStatus(ChangeUserStatusRequest $request, string $user): RedirectResponse
    {
        $this->changeUserStatus->execute(
            $request->user(),
            $user,
            UserStatus::from((string) $request->validated('status')),
        );

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    public function destroy(Request $request, string $user): RedirectResponse
    {
        $this->softDeleteUser->execute($request->user(), $user);

        return back()->with('success', 'User berhasil dihapus.');
    }
}
