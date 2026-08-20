<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\System\AccessControl\Application\Actions\CreateRole;
use App\Modules\System\AccessControl\Application\Actions\DeleteRole;
use App\Modules\System\AccessControl\Application\Actions\UpdateRoleAndPermissions;
use App\Modules\System\AccessControl\Application\DTO\PaginatedPermissionData;
use App\Modules\System\AccessControl\Application\DTO\PaginatedRoleData;
use App\Modules\System\AccessControl\Application\DTO\PermissionData;
use App\Modules\System\AccessControl\Application\DTO\RoleData;
use App\Modules\System\AccessControl\Application\Queries\GetRole;
use App\Modules\System\AccessControl\Application\Queries\ListPermissions;
use App\Modules\System\AccessControl\Application\Queries\ListRoles;
use App\Modules\System\AccessControl\Domain\Exceptions\ProtectedRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AccessControl\Presentation\Requests\ListPermissionsApiRequest;
use App\Modules\System\AccessControl\Presentation\Requests\ListRolesApiRequest;
use App\Modules\System\AccessControl\Presentation\Requests\StoreRoleApiRequest;
use App\Modules\System\AccessControl\Presentation\Requests\UpdateRoleApiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AccessControlApiController implements HasMiddleware
{
    public function __construct(
        private ListRoles $listRoles,
        private GetRole $getRole,
        private ListPermissions $listPermissions,
        private CreateRole $createRole,
        private UpdateRoleAndPermissions $updateRoleAndPermissions,
        private DeleteRole $deleteRole,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Role::class, only: ['roles', 'role', 'permissions', 'update', 'destroy']),
            new Middleware('can:create,'.Role::class, only: ['store']),
        ];
    }

    public function roles(ListRolesApiRequest $request): JsonResponse
    {
        $result = $this->listRoles->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar role berhasil dibaca.',
            array_map(static fn (RoleData $role): array => $role->toArray(), $result->data),
            $this->paginationMeta($result),
        );
    }

    public function role(Request $request, string $role): JsonResponse
    {
        abort_if(($data = $this->getRole->execute($role)) === null, 404);

        return $this->responses->success(
            $request,
            'Detail role berhasil dibaca.',
            $data->toArray(),
        );
    }

    public function permissions(ListPermissionsApiRequest $request): JsonResponse
    {
        $result = $this->listPermissions->execute($request->toFilter());
        $permissions = array_map(static function (PermissionData $permission): array {
            return [
                ...$permission->toArray(),
                'module' => str($permission->name)->before('.')->toString(),
            ];
        }, $result->data);

        return $this->responses->success(
            $request,
            'Daftar permission berhasil dibaca.',
            $permissions,
            $this->paginationMeta($result),
        );
    }

    public function store(StoreRoleApiRequest $request): JsonResponse
    {
        $correlationId = $this->responses->correlationId($request);
        $role = $this->createRole->execute($request->user(), $request->name(), $correlationId);

        return $this->responses->success(
            $request,
            'Role berhasil dibuat.',
            $role->toArray(),
            status: 201,
        );
    }

    public function update(UpdateRoleApiRequest $request, string $role): JsonResponse
    {
        $current = $this->getRole->execute($role);
        abort_if($current === null, 404);

        if ($current->isProtected) {
            throw new ProtectedRoleMutation;
        }

        $updated = $this->updateRoleAndPermissions->execute(
            $request->user(),
            $role,
            $request->name(),
            $request->permissions(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Role berhasil diperbarui.',
            $updated->toArray(),
        );
    }

    public function destroy(Request $request, string $role): JsonResponse
    {
        $current = $this->getRole->execute($role);
        abort_if($current === null, 404);

        if ($current->isProtected) {
            throw new ProtectedRoleMutation;
        }

        $this->deleteRole->execute(
            $request->user(),
            $role,
            $this->responses->correlationId($request),
        );

        return $this->responses->success($request, 'Role berhasil dihapus.', null);
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedRoleData|PaginatedPermissionData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
