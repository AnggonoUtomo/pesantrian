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
        $this->assertUlidColumn('users', 'id');
        $this->assertUlidColumn('roles', 'id');
        $this->assertUlidColumn('permissions', 'id');
        $this->assertUlidColumn('model_has_roles', 'model_id');

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

    public function test_existing_baseline_tables_do_not_drift_back_to_integer_ids(): void
    {
        foreach ([
            ['users', 'id'],
            ['passkeys', 'id'],
            ['passkeys', 'user_id'],
            ['jobs', 'id'],
            ['roles', 'id'],
            ['permissions', 'id'],
            ['model_has_roles', 'role_id'],
            ['model_has_roles', 'model_id'],
            ['model_has_permissions', 'permission_id'],
            ['model_has_permissions', 'model_id'],
            ['role_has_permissions', 'role_id'],
            ['role_has_permissions', 'permission_id'],
        ] as [$table, $column]) {
            $this->assertUlidColumn($table, $column);
        }
    }

    private function assertUlidColumn(string $table, string $column): void
    {
        self::assertContains(
            Schema::getColumnType($table, $column),
            ['string', 'varchar', 'bpchar'],
            "$table.$column harus memakai tipe string ULID.",
        );
    }
}
