<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Database\Seeders;

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        /** @var array<int, array{key: string}> $permissionDefinitions */
        $permissionDefinitions = require __DIR__.'/../../permissions.php';
        $permissionKeys = [];

        foreach ($permissionDefinitions as $permissionDefinition) {
            $permissionKeys[] = $permissionDefinition['key'];
        }

        foreach ($permissionKeys as $permissionKey) {
            Permission::firstOrCreate([
                'name' => $permissionKey,
                'guard_name' => 'web',
            ]);
        }

        $allPermissions = Permission::whereIn('name', $permissionKeys)->get();

        $superSystem = Role::firstOrCreate([
            'name' => 'SuperSystem',
            'guard_name' => 'web',
        ]);
        $superSystem->syncPermissions($allPermissions);

        $securityAdmin = Role::firstOrCreate([
            'name' => 'SecurityAdmin',
            'guard_name' => 'web',
        ]);
        $securityAdmin->syncPermissions($allPermissions);

        $configuredPassword = config('access-control.dummy_password');
        $password = $configuredPassword ?: Str::password(32);

        $superSystemUser = User::firstOrCreate(
            ['email' => 'super-system@example.test'],
            [
                'name' => 'Super System Demo',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
        if ($configuredPassword) {
            $superSystemUser->forceFill(['password' => $configuredPassword])->save();
        }
        $superSystemUser->syncRoles([$superSystem]);

        $securityAdminUser = User::firstOrCreate(
            ['email' => 'security-admin@example.test'],
            [
                'name' => 'Security Admin Demo',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
        if ($configuredPassword) {
            $securityAdminUser->forceFill(['password' => $configuredPassword])->save();
        }
        $securityAdminUser->syncRoles([$securityAdmin]);
    }
}
