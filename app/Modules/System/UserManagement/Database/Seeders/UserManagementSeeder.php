<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Database\Seeders;

use App\Models\User;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
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
        foreach (range(1, 50) as $number) {
            $email = sprintf('user-management-dummy-%02d@example.test', $number);
            $attributes = User::factory()
                ->state([
                    'email' => $email,
                    'password' => $password,
                    'status' => UserStatus::cases()[($number - 1) % 3]->value,
                ])
                ->make()
                ->getAttributes();
            $user = User::firstOrCreate(['email' => $email], $attributes);
            $user->syncRoles([$securityAdminRole]);
        }
    }
}
