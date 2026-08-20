<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Models\User;
use App\Modules\System\UserManagement\Application\Actions\CreateUser;
use App\Modules\System\UserManagement\Application\Actions\EndImpersonation;
use App\Modules\System\UserManagement\Application\Actions\MutateUserPermission;
use App\Modules\System\UserManagement\Application\Actions\MutateUserRole;
use App\Modules\System\UserManagement\Application\Actions\SoftDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\StartImpersonation;
use App\Modules\System\UserManagement\Application\Actions\UpdateUserProfileAndStatus;
use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;
use App\Modules\System\UserManagement\Application\Queries\GetUser;
use App\Modules\System\UserManagement\Application\Queries\ListUsers;
use App\Modules\System\UserManagement\Presentation\Requests\AssignUserPermissionApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\AssignUserRoleApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\DeleteUserApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\ListUsersApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\StartImpersonationApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\StoreUserApiRequest;
use App\Modules\System\UserManagement\Presentation\Requests\UpdateUserApiRequest;
use App\Modules\System\UserManagement\Presentation\Resources\UserApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class UserApiController implements HasMiddleware
{
    public function __construct(
        private ListUsers $listUsers,
        private GetUser $getUser,
        private CreateUser $createUser,
        private StartImpersonation $startImpersonation,
        private EndImpersonation $endImpersonation,
        private MutateUserRole $mutateUserRole,
        private MutateUserPermission $mutateUserPermission,
        private UpdateUserProfileAndStatus $updateUserProfileAndStatus,
        private SoftDeleteUser $softDeleteUser,
        private UserRuntimeSettings $runtimeSettings,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.User::class, only: ['index', 'show']),
            new Middleware('can:create,'.User::class, only: ['store']),
            new Middleware('can:mutate,'.User::class, only: ['update']),
            new Middleware('can:deleteAny,'.User::class, only: ['destroy']),
            new Middleware('can:assignRole,'.User::class, only: ['assignRole', 'revokeRole']),
            new Middleware('can:assignPermission,'.User::class, only: ['assignPermission', 'revokePermission']),
            new Middleware('can:startImpersonation,'.User::class, only: ['startImpersonation']),
        ];
    }

    public function index(ListUsersApiRequest $request): JsonResponse
    {
        $result = $this->listUsers->execute($request->toFilter($this->runtimeSettings));
        $users = array_map(
            static fn ($user): array => (new UserApiResource($user))->toArray($request),
            $result->data,
        );

        return $this->responses->success(
            $request,
            'Daftar user berhasil dibaca.',
            $users,
            [
                'current_page' => $result->currentPage,
                'per_page' => $result->perPage,
                'total' => $result->total,
                'last_page' => $result->lastPage,
            ],
        );
    }

    public function show(Request $request, string $user): JsonResponse
    {
        abort_if(($data = $this->getUser->execute($user)) === null, 404);

        return $this->responses->success(
            $request,
            'Detail user berhasil dibaca.',
            (new UserApiResource($data))->toArray($request),
        );
    }

    public function store(StoreUserApiRequest $request): JsonResponse
    {
        $correlationId = $this->responses->correlationId($request);
        $user = $this->createUser->execute(
            $request->user(),
            $request->toData(),
            $correlationId,
        );

        return $this->responses->success(
            $request,
            'User berhasil dibuat.',
            (new UserApiResource($user))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateUserApiRequest $request, string $user): JsonResponse
    {
        $current = $this->getUser->execute($user);
        abort_if($current === null, 404);
        $correlationId = $this->responses->correlationId($request);
        $updated = $this->updateUserProfileAndStatus->execute(
            $request->user(),
            $user,
            $request->hasProfileChanges() ? $request->profileData($current) : null,
            $request->status(),
            $correlationId,
        );

        return $this->responses->success(
            $request,
            'User berhasil diperbarui.',
            (new UserApiResource($updated))->toArray($request),
        );
    }

    public function destroy(DeleteUserApiRequest $request, string $user): JsonResponse
    {
        abort_if($this->getUser->execute($user) === null, 404);
        $correlationId = $this->responses->correlationId($request);
        $this->softDeleteUser->execute(
            $request->user(),
            $user,
            $request->reason(),
            $correlationId,
        );

        return $this->responses->success(
            $request,
            'User berhasil diarsipkan.',
            null,
        );
    }

    public function assignRole(AssignUserRoleApiRequest $request, User $user): JsonResponse
    {
        $updated = $this->mutateUserRole->assign(
            $request->user(),
            $user,
            $request->role(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Role user berhasil diberikan.',
            (new UserApiResource($updated))->toArray($request),
        );
    }

    public function revokeRole(Request $request, User $user, string $role): JsonResponse
    {
        $updated = $this->mutateUserRole->revoke(
            $request->user(),
            $user,
            $role,
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Role user berhasil dicabut.',
            (new UserApiResource($updated))->toArray($request),
        );
    }

    public function assignPermission(AssignUserPermissionApiRequest $request, User $user): JsonResponse
    {
        $updated = $this->mutateUserPermission->assign(
            $request->user(),
            $user,
            $request->permission(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Direct permission user berhasil diberikan.',
            (new UserApiResource($updated))->toArray($request),
        );
    }

    public function revokePermission(Request $request, User $user, string $permission): JsonResponse
    {
        $updated = $this->mutateUserPermission->revoke(
            $request->user(),
            $user,
            $permission,
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Direct permission user berhasil dicabut.',
            (new UserApiResource($updated))->toArray($request),
        );
    }

    public function startImpersonation(StartImpersonationApiRequest $request, User $user): JsonResponse
    {
        $correlationId = $this->responses->correlationId($request);
        $state = $this->startImpersonation->execute(
            $request->user(),
            $request->toData((string) $user->getKey(), $correlationId),
        );

        return $this->responses->success(
            $request,
            'Impersonation berhasil dimulai.',
            $state->toArray(),
        );
    }

    public function endImpersonation(Request $request): JsonResponse
    {
        $this->endImpersonation->execute($request->user());

        return $this->responses->success(
            $request,
            'Impersonation berhasil diakhiri.',
            null,
        );
    }
}
