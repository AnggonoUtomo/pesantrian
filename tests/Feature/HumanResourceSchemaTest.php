<?php

use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeUnitAssignmentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('memiliki schema employees dan employee unit assignments dengan ULID dan field minimum', function (): void {
    expect(Schema::hasTable('employees'))->toBeTrue()
        ->and(Schema::hasColumns('employees', [
            'id',
            'employee_no',
            'name',
            'preferred_name',
            'employment_type',
            'position',
            'status',
            'joined_on',
            'left_on',
            'primary_unit_id',
            'notes',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('employee_unit_assignments'))->toBeTrue()
        ->and(Schema::hasColumns('employee_unit_assignments', [
            'id',
            'employee_id',
            'organization_unit_id',
            'role',
            'starts_on',
            'ends_on',
            'is_primary',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('employees', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('employees', 'primary_unit_id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('employee_unit_assignments', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('employee_unit_assignments', 'employee_id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('employee_unit_assignments', 'organization_unit_id'))->toBeIn(['string', 'varchar']);
});

it('membuat employee dan assignment record dengan ULID otomatis serta relasi minimum', function (): void {
    $unitId = (string) Str::ulid();
    DB::table('organization_units')->insert([
        'id' => $unitId,
        'parent_id' => null,
        'code' => 'MA',
        'name' => 'Madrasah Aliyah',
        'type' => 'education_unit',
        'status' => 'active',
        'location_name' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'EMP-2026-0012',
        'name' => 'Ahmad Fauzi',
        'preferred_name' => 'Ustadz Ahmad',
        'employment_type' => 'ustadz',
        'position' => 'Pengajar Kitab',
        'status' => 'active',
        'joined_on' => '2026-07-01',
        'left_on' => null,
        'primary_unit_id' => $unitId,
        'notes' => null,
    ]);

    $assignment = EmployeeUnitAssignmentRecord::query()->create([
        'employee_id' => $employee->getKey(),
        'organization_unit_id' => $unitId,
        'role' => 'ustadz',
        'starts_on' => '2026-07-01',
        'ends_on' => null,
        'is_primary' => true,
    ]);

    expect((string) $employee->getKey())->toHaveLength(26)
        ->and((string) $assignment->getKey())->toHaveLength(26)
        ->and($employee->assignments)->toHaveCount(1)
        ->and($assignment->employee->is($employee))->toBeTrue()
        ->and($employee->joined_on->toDateString())->toBe('2026-07-01')
        ->and($assignment->is_primary)->toBeTrue();

    $this->assertDatabaseHas('employees', [
        'id' => $employee->getKey(),
        'employee_no' => 'EMP-2026-0012',
        'employment_type' => 'ustadz',
        'status' => 'active',
        'primary_unit_id' => $unitId,
    ]);
    $this->assertDatabaseHas('employee_unit_assignments', [
        'id' => $assignment->getKey(),
        'employee_id' => $employee->getKey(),
        'organization_unit_id' => $unitId,
        'role' => 'ustadz',
        'is_primary' => true,
    ]);
});
