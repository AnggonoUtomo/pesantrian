<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class AccessControlSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_permission_role_dan_user_dummy(): void
    {
        $this->seed(AccessControlSeeder::class);

        $this->assertSame(11, Permission::count());
        $this->assertTrue(Role::where('name', 'SuperSystem')->exists());
        $this->assertTrue(Role::where('name', 'SecurityAdmin')->exists());

        $securityAdmin = User::where('email', 'security-admin@example.test')->firstOrFail();

        $this->assertTrue($securityAdmin->hasRole('SecurityAdmin'));
        $this->assertTrue($securityAdmin->hasPermissionTo('access_control.role.manage'));
        $this->assertTrue($securityAdmin->hasPermissionTo('user.impersonate'));
    }

    public function test_seeder_idempotent_dan_tidak_menduplikasi_data(): void
    {
        $this->seed(AccessControlSeeder::class);
        $this->seed(AccessControlSeeder::class);

        $this->assertSame(11, Permission::count());
        $this->assertSame(2, Role::count());
        $this->assertSame(2, User::whereIn('email', [
            'super-system@example.test',
            'security-admin@example.test',
        ])->count());
    }

    public function test_command_module_menjalankan_seeder_access_control(): void
    {
        $this->artisan('access-control:seed')
            ->assertSuccessful()
            ->expectsOutput('Seeder AccessControl selesai.');

        $this->assertSame(11, Permission::count());
        $this->assertSame(2, Role::count());
    }

    public function test_database_seeder_global_menjalankan_seeder_module(): void
    {
        $this->seed();

        $this->assertSame(11, Permission::count());
        $this->assertSame(2, Role::count());
        $this->assertSame(12, User::count());
    }

    public function test_seeder_tidak_membuat_dummy_data_di_production(): void
    {
        Config::set('app.env', 'production');

        $this->seed(AccessControlSeeder::class);

        $this->assertSame(0, Permission::count());
        $this->assertSame(0, Role::count());
        $this->assertSame(0, User::count());
    }
}
