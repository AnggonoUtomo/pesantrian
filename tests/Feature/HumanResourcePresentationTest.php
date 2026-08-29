<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('menolak actor tanpa permission human resource view', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('human-resource.employees.index'))
        ->assertForbidden();
});

it('menampilkan halaman Inertia daftar employee human resource', function (): void {
    $view = Permission::create(['name' => 'human_resource.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $unitId = createHumanResourcePresentationOrganizationUnit('PST', 'Pesantren Saka');
    EmployeeRecord::query()->create([
        'primary_unit_id' => $unitId,
        'employee_no' => 'HR-UI-001',
        'name' => 'Ahmad UI',
        'preferred_name' => 'Ahmad',
        'employment_type' => 'ustadz',
        'position' => 'Pengajar Tahfidz',
        'status' => 'active',
        'joined_on' => '2026-08-01',
    ]);
    EmployeeRecord::query()->create([
        'employee_no' => 'HR-UI-002',
        'name' => 'Budi Nonaktif',
        'employment_type' => 'staff',
        'status' => 'inactive',
    ]);

    $this->actingAs($actor)
        ->get(route('human-resource.employees.index', [
            'search' => 'Ahmad',
            'filter' => [
                'status' => 'active',
                'employment_type' => 'ustadz',
                'primary_unit_id' => $unitId,
            ],
            'per_page' => 10,
            'sort' => 'employee_no',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('HumanResource/HumanResource/pages/Index')
            ->where('employees.data.0.employee_no', 'HR-UI-001')
            ->where('employees.data.0.name', 'Ahmad UI')
            ->where('employees.data.0.employment_type', 'ustadz')
            ->where('employees.data.0.status', 'active')
            ->where('employees.data.0.primary_unit_id', $unitId)
            ->where('employees.data.0.primary_unit.name', 'Pesantren Saka')
            ->where('employees.meta.total', 1)
            ->where('filters.search', 'Ahmad')
            ->where('filters.filter.status', 'active')
            ->where('filters.filter.employment_type', 'ustadz')
            ->where('filters.filter.primary_unit_id', $unitId)
            ->where('filters.per_page', '10')
            ->where('filters.sort', 'employee_no')
            ->where('pagination.defaultPerPage', 25));
});

function createHumanResourcePresentationOrganizationUnit(string $code, string $name): string
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
