<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('mengembalikan list employee dengan filter pagination sort dan envelope canonical', function (): void {
    $view = Permission::create(['name' => 'human_resource.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $unitId = createHumanResourceOrganizationUnit('MDN', 'Madrasah Diniyah');

    $employee = EmployeeRecord::query()->create([
        'primary_unit_id' => $unitId,
        'employee_no' => 'HR-001',
        'name' => 'Ahmad Saka',
        'preferred_name' => 'Ahmad',
        'employment_type' => 'ustadz',
        'position' => 'Pengajar Tahfidz',
        'status' => 'active',
        'joined_on' => '2026-07-01',
        'notes' => 'Pengampu kelas sore',
    ]);
    EmployeeRecord::query()->create([
        'employee_no' => 'HR-002',
        'name' => 'Budi Arsip',
        'employment_type' => 'administration_staff',
        'status' => 'inactive',
    ]);

    $query = http_build_query([
        'search' => 'Ahmad',
        'filter' => [
            'status' => 'active',
            'employment_type' => 'ustadz',
            'primary_unit_id' => $unitId,
        ],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'employee_no',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.human-resource.employees.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar employee berhasil dibaca.')
        ->assertJsonPath('data.0.id', $employee->id)
        ->assertJsonPath('data.0.primary_unit_id', $unitId)
        ->assertJsonPath('data.0.employee_no', 'HR-001')
        ->assertJsonPath('data.0.name', 'Ahmad Saka')
        ->assertJsonPath('data.0.preferred_name', 'Ahmad')
        ->assertJsonPath('data.0.employment_type', 'ustadz')
        ->assertJsonPath('data.0.position', 'Pengajar Tahfidz')
        ->assertJsonPath('data.0.status', 'active')
        ->assertJsonPath('data.0.joined_on', '2026-07-01')
        ->assertJsonPath('data.0.primary_unit.id', $unitId)
        ->assertJsonPath('data.0.primary_unit.code', 'MDN')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'primary_unit_id',
                'primary_unit',
                'employee_no',
                'name',
                'preferred_name',
                'employment_type',
                'position',
                'status',
                'joined_on',
                'left_on',
                'notes',
                'created_at',
                'updated_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('membuat dan memperbarui employee melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $unitId = createHumanResourceOrganizationUnit('PST', 'Pesantren Saka');

    $created = $this->actingAs($actor)->postJson(route('api.v1.human-resource.employees.store'), [
        'primary_unit_id' => $unitId,
        'employee_no' => 'HR-010',
        'name' => 'Fatimah Zahra',
        'preferred_name' => 'Fatimah',
        'employment_type' => 'teacher',
        'position' => 'Guru Kelas',
        'status' => 'active',
        'joined_on' => '2026-08-01',
        'notes' => 'Koordinator kelas awal',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee berhasil dibuat.')
        ->assertJsonPath('data.primary_unit_id', $unitId)
        ->assertJsonPath('data.employee_no', 'HR-010')
        ->assertJsonPath('data.name', 'Fatimah Zahra');

    $employeeId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.human-resource.employees.update', $employeeId), [
        'name' => 'Fatimah Saka',
        'status' => 'inactive',
        'left_on' => '2026-08-20',
        'notes' => 'Nonaktif administratif',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Employee berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Fatimah Saka')
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.left_on', '2026-08-20');

    $this->assertDatabaseHas('employees', [
        'id' => $employeeId,
        'primary_unit_id' => $unitId,
        'employee_no' => 'HR-010',
        'name' => 'Fatimah Saka',
        'status' => 'inactive',
        'left_on' => '2026-08-20 00:00:00',
    ]);
});

it('menolak guest actor tanpa permission dan payload invalid dengan envelope canonical', function (): void {
    $this->getJson(route('api.v1.human-resource.employees.index'))
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)
        ->getJson(route('api.v1.human-resource.employees.index'))
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    EmployeeRecord::query()->create([
        'employee_no' => 'HR-020',
        'name' => 'Employee Existing',
        'employment_type' => 'staff',
        'status' => 'active',
    ]);

    $this->actingAs($actor)->postJson(route('api.v1.human-resource.employees.store'), [
        'primary_unit_id' => (string) Str::ulid(),
        'employee_no' => 'HR-020',
        'name' => 'Duplikat',
        'employment_type' => 'unknown',
        'status' => 'active',
        'left_on' => '2026-08-20',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['primary_unit_id', 'employee_no', 'employment_type', 'left_on']]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.human-resource.employees.update', (string) Str::ulid()),
        ['name' => 'Missing'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

function createHumanResourceOrganizationUnit(string $code, string $name): string
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
