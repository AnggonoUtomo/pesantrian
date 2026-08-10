<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use Illuminate\Contracts\Auth\Authenticatable;
use ReflectionClass;

it('memiliki permission identity UserManagement yang unik dan sensitif terdefinisi', function (): void {
    $permissions = require app_path('Modules/System/UserManagement/permissions.php');
    $keys = array_column($permissions, 'key');

    expect($permissions)->toHaveCount(9)
        ->and($keys)->toHaveCount(count(array_unique($keys)))
        ->and($keys)->toContain(
            'user.view',
            'user.create',
            'user.update',
            'user.status.manage',
            'user.delete',
            'user.restore',
            'user.force.delete',
            'user.impersonate',
        );

    foreach ($permissions as $permission) {
        expect($permission['module'])->toBe('UserManagement')
            ->and($permission)->toHaveKeys(['key', 'description', 'module', 'sensitive']);
    }

    $impersonation = collect($permissions)->firstWhere('key', 'user.impersonate');

    expect($impersonation['sensitive'])->toBeTrue();

    $restore = collect($permissions)->firstWhere('key', 'user.restore');
    $forceDelete = collect($permissions)->firstWhere('key', 'user.force.delete');

    expect($restore['sensitive'])->toBeTrue()
        ->and($forceDelete['sensitive'])->toBeTrue();
});

it('role assignment memakai contract publik tanpa dependency private Spatie', function (): void {
    $reflection = new ReflectionClass(RoleAssignmentCapability::class);
    $methods = array_map(static fn ($method): string => $method->getName(), $reflection->getMethods());

    expect($reflection->isInterface())->toBeTrue()
        ->and($methods)->toEqualCanonicalizing(['assignRole', 'revokeRole', 'syncRoles']);

    foreach ($reflection->getMethods() as $method) {
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            expect($type?->getName())->not->toContain('Spatie');
        }
    }

    expect($reflection->getMethod('assignRole')->getParameters()[0]->getType()?->getName())
        ->toBe(Authenticatable::class);
});
