<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Controllers;

use App\Models\User;
use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\UserManagement\Application\Actions\AssignUserRole;
use App\Modules\System\UserManagement\Application\Actions\BulkUserLifecycle;
use App\Modules\System\UserManagement\Application\Actions\ChangeUserStatus;
use App\Modules\System\UserManagement\Application\Actions\CreateUser;
use App\Modules\System\UserManagement\Application\Actions\ForceDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\RestoreUser;
use App\Modules\System\UserManagement\Application\Actions\SoftDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\StartImpersonation;
use App\Modules\System\UserManagement\Application\Actions\UpdateUser;
use App\Modules\System\UserManagement\Application\Actions\UpdateUserAvatar;
use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\ImpersonationRequestData;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Application\Queries\GetUser;
use App\Modules\System\UserManagement\Application\Queries\ListUsers;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use App\Modules\System\UserManagement\Presentation\Requests\AssignUserRoleRequest;
use App\Modules\System\UserManagement\Presentation\Requests\BulkUserLifecycleRequest;
use App\Modules\System\UserManagement\Presentation\Requests\ChangeUserStatusRequest;
use App\Modules\System\UserManagement\Presentation\Requests\ListUsersRequest;
use App\Modules\System\UserManagement\Presentation\Requests\StartImpersonationRequest;
use App\Modules\System\UserManagement\Presentation\Requests\StoreUserRequest;
use App\Modules\System\UserManagement\Presentation\Requests\UpdateUserAvatarRequest;
use App\Modules\System\UserManagement\Presentation\Requests\UpdateUserRequest;
use App\Modules\System\UserManagement\Presentation\Resources\UserResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UserController implements HasMiddleware
{
    public function __construct(
        private readonly ListUsers $listUsers,
        private readonly GetUser $getUser,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly UpdateUserAvatar $updateUserAvatar,
        private readonly ChangeUserStatus $changeUserStatus,
        private readonly SoftDeleteUser $softDeleteUser,
        private readonly RestoreUser $restoreUser,
        private readonly ForceDeleteUser $forceDeleteUser,
        private readonly StartImpersonation $startImpersonation,
        private readonly ImpersonationSession $impersonationSession,
        private readonly AssignUserRole $assignUserRole,
        private readonly BulkUserLifecycle $bulkUserLifecycle,
        private readonly RoleCatalogCapability $roleCatalog,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:user.view', only: ['index', 'show']),
            new Middleware('can:view,user', only: ['avatar']),
            new Middleware('can:user.create', only: ['store']),
            new Middleware('can:update,user', only: ['update']),
            new Middleware('can:update,user', only: ['updateAvatar']),
            new Middleware('can:update,user', only: ['deleteAvatar']),
            new Middleware('can:update,user', only: ['assignRole']),
            new Middleware('can:user.status.manage', only: ['changeStatus']),
            new Middleware('can:user.delete', only: ['destroy']),
            new Middleware('can:user.delete', only: ['bulkDestroy']),
            new Middleware('can:restore,user', only: ['restore']),
            new Middleware('can:forceDelete,user', only: ['forceDelete']),
            new Middleware('can:user.force.delete', only: ['bulkForceDelete']),
            new Middleware('can:impersonate,user', only: ['impersonate']),
        ];
    }

    public function index(ListUsersRequest $request): Response
    {
        $filters = $request->validated();
        $filter = UserListFilter::from(
            $filters['search'] ?? null,
            $filters['status'] ?? null,
            $filters['role'] ?? null,
            $filters['archive'] ?? null,
            isset($filters['page']) ? (int) $filters['page'] : null,
            isset($filters['per_page']) ? (int) $filters['per_page'] : null,
        );
        $result = $this->listUsers->execute($filter);
        $users = array_map(
            static fn ($user): array => (new UserResource($user))->toArray($request),
            $result->data,
        );

        return Inertia::render('System/UserManagement/pages/Index', [
            'users' => $users,
            'filters' => [
                'search' => $filter->search,
                'status' => $filter->status?->value,
                'role' => $filter->role,
                'archive' => $filter->archive,
                'page' => $filter->page,
                'perPage' => $filter->perPage,
            ],
            'pagination' => [
                'total' => $result->total,
                'currentPage' => $result->currentPage,
                'lastPage' => $result->lastPage,
                'perPage' => $result->perPage,
            ],
            'roles' => array_map(
                static fn ($role): array => $role->toArray(),
                $this->roleCatalog->listRoles(),
            ),
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
            status: UserStatus::from((string) ($data['status'] ?? 'active')),
            role: isset($data['role']) ? (string) $data['role'] : null,
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User berhasil dibuat.']);

        return back();
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $this->updateUser->execute($request->user(), (string) $user->getKey(), new UpdateUserData(
            name: (string) $data['name'],
            email: (string) $data['email'],
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User berhasil diperbarui.']);

        return back();
    }

    public function updateAvatar(UpdateUserAvatarRequest $request, User $user): RedirectResponse
    {
        /** @var UploadedFile $avatar */
        $avatar = $request->file('avatar');
        $this->updateUserAvatar->execute($request->user(), $user, $avatar);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar user berhasil diperbarui.']);

        return back();
    }

    public function avatar(User $user): BinaryFileResponse
    {
        abort_unless($user->getFirstMedia('avatar') !== null, 404);

        return response()->file($user->getFirstMedia('avatar')->getPath());
    }

    public function deleteAvatar(Request $request, User $user): RedirectResponse
    {
        $this->updateUserAvatar->delete($request->user(), $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar user berhasil dihapus.']);

        return back();
    }

    public function changeStatus(ChangeUserStatusRequest $request, string $user): RedirectResponse
    {
        $this->changeUserStatus->execute(
            $request->user(),
            $user,
            UserStatus::from((string) $request->validated('status')),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Status user berhasil diperbarui.']);

        return back();
    }

    public function assignRole(AssignUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->assignUserRole->execute(
            $request->user(),
            $user,
            (string) $request->validated('role'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Role user berhasil diperbarui.']);

        return back();
    }

    public function destroy(Request $request, string $user): RedirectResponse
    {
        $this->softDeleteUser->execute($request->user(), $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User berhasil diarsipkan.']);

        return back();
    }

    public function bulkDestroy(BulkUserLifecycleRequest $request): RedirectResponse
    {
        $result = $this->bulkUserLifecycle->archive(
            $request->user(),
            $request->validated('user_ids'),
        );

        Inertia::flash('toast', $result->completed
            ? ['type' => 'success', 'message' => "{$result->processed} user berhasil diarsipkan."]
            : ['type' => 'error', 'message' => $result->message]);

        return back();
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        $this->restoreUser->execute($request->user(), $user->getKey());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User berhasil dipulihkan.']);

        return back();
    }

    public function forceDelete(Request $request, User $user): RedirectResponse
    {
        $this->forceDeleteUser->execute($request->user(), $user->getKey());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User dihapus permanen.']);

        return back();
    }

    public function bulkForceDelete(BulkUserLifecycleRequest $request): RedirectResponse
    {
        $result = $this->bulkUserLifecycle->forceDelete(
            $request->user(),
            $request->validated('user_ids'),
        );

        Inertia::flash('toast', $result->completed
            ? ['type' => 'success', 'message' => "{$result->processed} user berhasil dihapus permanen."]
            : ['type' => 'error', 'message' => $result->message]);

        return back();
    }

    public function impersonate(StartImpersonationRequest $request, User $user): RedirectResponse
    {
        $this->startImpersonation->execute(
            $request->user(),
            new ImpersonationRequestData(
                targetUserId: $user->getKey(),
                reason: (string) $request->validated('reason'),
            ),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Impersonation dimulai.']);

        return to_route('system.dashboard');
    }

    public function leaveImpersonation(Request $request): RedirectResponse
    {
        $this->impersonationSession->leave($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Impersonation selesai.']);

        return to_route('system.dashboard');
    }
}
