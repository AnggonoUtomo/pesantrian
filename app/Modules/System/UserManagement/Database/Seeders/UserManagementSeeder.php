<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class UserManagementSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $password = config('access_control.dummy_password') ?: Str::password(32);
        $securityAdminRole = 'SecurityAdmin';
        $users = [
            ['name' => 'Alya Pratama', 'email' => 'alya.pratama@example.test', 'status' => 'active'],
            ['name' => 'Bima Santoso', 'email' => 'bima.santoso@example.test', 'status' => 'active'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@example.test', 'status' => 'inactive'],
            ['name' => 'Danu Wijaya', 'email' => 'danu.wijaya@example.test', 'status' => 'active'],
            ['name' => 'Eka Permata', 'email' => 'eka.permata@example.test', 'status' => 'suspended'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar.nugraha@example.test', 'status' => 'active'],
            ['name' => 'Gita Maharani', 'email' => 'gita.maharani@example.test', 'status' => 'inactive'],
            ['name' => 'Hadi Kurniawan', 'email' => 'hadi.kurniawan@example.test', 'status' => 'active'],
            ['name' => 'Intan Sari', 'email' => 'intan.sari@example.test', 'status' => 'suspended'],
            ['name' => 'Joko Firmansyah', 'email' => 'joko.firmansyah@example.test', 'status' => 'active'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'status' => $data['status'],
                    'email_verified_at' => now(),
                ],
            );

            $user->forceFill([
                'name' => $data['name'],
                'status' => $data['status'],
            ])->save();
            $user->syncRoles([$securityAdminRole]);
        }
    }
}
