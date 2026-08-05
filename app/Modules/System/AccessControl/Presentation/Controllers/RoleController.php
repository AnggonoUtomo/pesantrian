<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Controllers;

use App\Modules\System\AccessControl\Application\Actions\CreateRole;
use App\Modules\System\AccessControl\Application\Actions\DeleteRole;
use App\Modules\System\AccessControl\Application\Actions\SyncRolePermissions;
use App\Modules\System\AccessControl\Application\Queries\BuildAccessControlDashboard;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AccessControl\Presentation\Requests\StoreRoleRequest;
use App\Modules\System\AccessControl\Presentation\Requests\SyncRolePermissionsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final class RoleController implements HasMiddleware
{
    public function __construct(
        private readonly BuildAccessControlDashboard $dashboard,
        private readonly CreateRole $createRole,
        private readonly SyncRolePermissions $syncRolePermissions,
        private readonly DeleteRole $deleteRole,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Role::class, only: ['index']),
            new Middleware('can:system.dashboard.view', only: ['dashboard']),
            new Middleware('can:create,'.Role::class, only: ['store']),
            new Middleware('can:update,role', only: ['syncPermissions']),
            new Middleware('can:delete,role', only: ['destroy']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('System/AccessControl/pages/Index', $this->dashboard->execute()->toArray());
    }

    public function dashboard(): Response
    {
        return Inertia::render('System/Dashboard', $this->dashboard->execute()->toArray());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->createRole->execute($request->user(), (string) $request->string('name'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Role berhasil ditambahkan.',
        ]);

        return back();
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        /** @var array<int, string> $permissions */
        $permissions = $request->validated('permissions');
        $this->syncRolePermissions->execute($request->user(), $role, $permissions);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Permission role berhasil diperbarui.',
        ]);

        return back();
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->deleteRole->execute($request->user(), $role);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Role berhasil dihapus.',
        ]);

        return back();
    }
}
