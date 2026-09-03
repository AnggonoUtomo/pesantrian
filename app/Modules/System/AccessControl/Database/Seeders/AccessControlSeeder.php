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

        $this->syncDemoRole('OperatorPPDB', [
            'system.dashboard.view',
            'organization.view',
            'penerimaan_santri.view',
            'penerimaan_santri.manage',
            'penerimaan_santri.decide',
            'santri.view',
        ]);
        $this->syncDemoRole('OperatorSantri', [
            'system.dashboard.view',
            'organization.view',
            'penerimaan_santri.view',
            'santri.view',
            'santri.manage',
            'santri.lifecycle',
            'santri.archive',
            'kelas_rombel.view',
        ]);
        $this->syncDemoRole('OperatorAkademik', [
            'system.dashboard.view',
            'organization.view',
            'academic_period.view',
            'academic_period.manage',
            'human_resource.view',
            'santri.view',
            'kelas_rombel.view',
            'kelas_rombel.manage',
            'kelas_rombel.placement',
            'kelas_rombel.archive',
        ]);
        $this->syncDemoRole('OperatorSDM', [
            'system.dashboard.view',
            'organization.view',
            'human_resource.view',
            'human_resource.manage',
        ]);
        $this->syncDemoRole('Auditor', [
            'system.dashboard.view',
            'audit_log.view',
            'organization.view',
            'academic_period.view',
            'human_resource.view',
            'penerimaan_santri.view',
            'santri.view',
            'kelas_rombel.view',
            'system_setting.view',
        ]);
        $this->syncDemoRole('Viewer', [
            'system.dashboard.view',
            'organization.view',
            'academic_period.view',
            'human_resource.view',
            'penerimaan_santri.view',
            'santri.view',
            'kelas_rombel.view',
        ]);

        $configuredPassword = config('access-control.dummy_password') ?: config('access_control.dummy_password');
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

        foreach ($this->demoUsers() as [$email, $name, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );

            if ($configuredPassword) {
                $user->forceFill(['password' => $configuredPassword])->save();
            }

            $user->syncRoles([$role]);
        }
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

    /** @param list<string> $permissionNames */
    private function syncDemoRole(string $roleName, array $permissionNames): void
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    private function demoUsers(): array
    {
        return [
            ['operator-ppdb@example.test', 'Operator PPDB Demo', 'OperatorPPDB'],
            ['operator-santri@example.test', 'Operator Santri Demo', 'OperatorSantri'],
            ['operator-akademik@example.test', 'Operator Akademik Demo', 'OperatorAkademik'],
            ['operator-sdm@example.test', 'Operator SDM Demo', 'OperatorSDM'],
            ['auditor@example.test', 'Auditor Demo', 'Auditor'],
            ['viewer@example.test', 'Viewer Demo', 'Viewer'],
        ];
    }
}
