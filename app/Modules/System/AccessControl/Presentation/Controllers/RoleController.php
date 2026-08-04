<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Controllers;

use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final class RoleController implements HasMiddleware
{
    public function __construct(private readonly AuthorizeRoleMutation $authorizeRoleMutation) {}

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
        return Inertia::render('System/AccessControl/pages/Index', $this->dashboardData());
    }

    public function dashboard(): Response
    {
        return Inertia::render('System/Dashboard', $this->dashboardData());
    }

    /** @return array<string, mixed> */
    private function dashboardData(): array
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(static fn (Role $role): array => [
                'id' => $role->getKey(),
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'is_protected' => $role->name === 'SuperSystem',
            ])
            ->values()
            ->all();

        $permissionGroups = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name'])
            ->groupBy(static fn (Permission $permission): string => (string) str($permission->name)->before('.'))
            ->map(static function ($permissions, string $module): array {
                return [
                    'module' => $module,
                    'label' => str($module)->replace('_', ' ')->title()->toString(),
                    'permissions' => $permissions->map(static fn (Permission $permission): array => [
                        'id' => $permission->getKey(),
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'label' => str($permission->name)->after('.')->replace(['.', '_'], ' ')->title()->toString(),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'selectedRoleId' => $roles[0]['id'] ?? null,
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z0-9][A-Za-z0-9 _-]*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
        ]);

        $this->authorizeRoleMutation->ensureAllowed($request->user());

        Role::create([
            'name' => trim((string) $request->string('name')),
            'guard_name' => 'web',
        ]);

        return back()->with('status', 'Role berhasil ditambahkan.');
    }

    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()?->can('update', $role), 403);

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'distinct', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        return back()->with('status', 'Permission role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->name === 'SuperSystem', 403);

        $role->delete();

        return back()->with('status', 'Role berhasil dihapus.');
    }
}
