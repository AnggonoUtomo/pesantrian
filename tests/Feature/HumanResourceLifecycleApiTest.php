<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('mengaktifkan kembali employee inactive dan mengosongkan left_on', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-LC-001',
        'name' => 'Employee Inactive',
        'employment_type' => 'staff',
        'status' => 'inactive',
        'joined_on' => '2026-01-01',
        'left_on' => '2026-08-01',
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.activate', $employee->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee berhasil diaktifkan.')
        ->assertJsonPath('data.id', $employee->id)
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.left_on', null);

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'status' => 'active',
        'left_on' => null,
    ]);
});

it('menonaktifkan employee active secara aman dan mengecualikan dari list active', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $view = Permission::create(['name' => 'human_resource.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$manage, $view]);
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-LC-002',
        'name' => 'Employee Active',
        'employment_type' => 'teacher',
        'status' => 'active',
        'joined_on' => '2026-01-01',
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.deactivate', $employee->id),
        ['left_on' => '2026-08-20'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee berhasil dinonaktifkan.')
        ->assertJsonPath('data.id', $employee->id)
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.left_on', '2026-08-20');

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'status' => 'inactive',
        'left_on' => '2026-08-20 00:00:00',
    ]);

    $query = http_build_query([
        'filter' => ['status' => 'active'],
        'per_page' => 10,
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.human-resource.employees.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('data', []);
});

it('menolak deactivate saat employee masih punya assignment unit aktif', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $unitId = createHumanResourceLifecycleOrganizationUnit('LCU', 'Lifecycle Unit');
    $employee = EmployeeRecord::query()->create([
        'primary_unit_id' => $unitId,
        'employee_no' => 'HR-LC-003',
        'name' => 'Employee Assigned',
        'employment_type' => 'ustadz',
        'status' => 'active',
    ]);
    DB::table('employee_unit_assignments')->insert([
        'id' => (string) Str::ulid(),
        'employee_id' => $employee->id,
        'organization_unit_id' => $unitId,
        'role' => 'ustadz',
        'starts_on' => '2026-01-01',
        'ends_on' => null,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.deactivate', $employee->id),
        ['left_on' => '2026-08-20'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['employee']]);

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'status' => 'active',
    ]);
});

it('menolak lifecycle untuk actor tanpa permission dan payload invalid', function (): void {
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-LC-004',
        'name' => 'Employee Permission',
        'employment_type' => 'staff',
        'status' => 'active',
    ]);

    $this->patchJson(
        route('api.v1.human-resource.employees.deactivate', $employee->id),
        ['left_on' => '2026-08-20'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)->patchJson(
        route('api.v1.human-resource.employees.activate', $employee->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.deactivate', $employee->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['left_on']]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.activate', (string) Str::ulid()),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertNotFound()
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

function createHumanResourceLifecycleOrganizationUnit(string $code, string $name): string
{
    $id = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $id,
        'code' => $code,
        'name' => $name,
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}
