<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\UserManagement\Database\Seeders\UserManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class UserManagementSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_lima_puluh_user_dummy_dengan_status_bervariasi(): void
    {
        $this->seed(AccessControlSeeder::class);
        $this->seed(UserManagementSeeder::class);

        $this->assertSame(52, User::count());
        $dummyUsers = User::query()->where('email', 'like', 'user-management-dummy-%@example.test');

        $this->assertSame(17, (clone $dummyUsers)->where('status', 'active')->count());
        $this->assertSame(17, (clone $dummyUsers)->where('status', 'inactive')->count());
        $this->assertSame(16, (clone $dummyUsers)->where('status', 'suspended')->count());
        $this->assertSame(51, User::role('SecurityAdmin')->count());
    }

    public function test_seeder_idempotent_dan_global_database_seeder_memanggilnya(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(52, User::count());
        $this->assertSame(50, User::where('email', 'like', '%@example.test')
            ->where('email', 'not like', 'super-system@example.test')
            ->where('email', 'not like', 'security-admin@example.test')
            ->count());
    }

    public function test_seeder_tidak_membuat_dummy_user_di_production(): void
    {
        Config::set('app.env', 'production');

        $this->seed(UserManagementSeeder::class);

        $this->assertSame(0, User::count());
    }
}
