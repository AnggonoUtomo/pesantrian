<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AccessControlSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_uses_ulid_columns_and_user_can_receive_role(): void
    {
        self::assertContains(Schema::getColumnType('users', 'id'), ['string', 'varchar']);
        self::assertContains(Schema::getColumnType('roles', 'id'), ['string', 'varchar']);
        self::assertContains(Schema::getColumnType('permissions', 'id'), ['string', 'varchar']);
        self::assertContains(Schema::getColumnType('model_has_roles', 'model_id'), ['string', 'varchar']);

        $user = User::factory()->create();
        Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
        $user->assignRole('SuperSystem');

        self::assertTrue($user->hasRole('SuperSystem'));
        self::assertIsString($user->getKey());
    }

    public function test_permission_tables_keep_role_and_permission_foreign_keys(): void
    {
        self::assertTrue(Schema::hasColumn('role_has_permissions', 'role_id'));
        self::assertTrue(Schema::hasColumn('role_has_permissions', 'permission_id'));
        self::assertTrue(Schema::hasColumn('model_has_permissions', 'permission_id'));
    }
}
