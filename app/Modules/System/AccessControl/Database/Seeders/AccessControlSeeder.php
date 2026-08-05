<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Database\Seeders;

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use StarterKit\Modules\ModuleRegistry;

final class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $permissionKeys = $this->permissionKeys();

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

        $configuredPassword = config('access_control.dummy_password');
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

    /** @return list<string> */
    private function permissionKeys(): array
    {
        $keys = [];
        $discovery = app(ModuleRegistry::class)->discover(app_path('Modules'));

        if ($discovery['diagnostics'] !== []) {
            throw new \UnexpectedValueException('Permission module tidak valid.');
        }

        foreach ($discovery['modules'] as $module) {
            $path = base_path($module->path.'/'.$module->permissionSource);
            $definitions = require $path;

            if (! is_array($definitions)) {
                throw new \UnexpectedValueException("Permission source [$path] harus berupa array.");
            }

            foreach ($definitions as $definition) {
                if (! is_array($definition) || ! isset($definition['key']) || ! is_string($definition['key'])) {
                    throw new \UnexpectedValueException('Setiap permission definition wajib memiliki key string.');
                }

                $keys[] = $definition['key'];
            }
        }

        return array_values(array_unique($keys));
    }
}
