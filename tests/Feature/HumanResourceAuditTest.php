<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('mencatat audit create dan update employee dengan metadata aman', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $createCorrelationId = (string) Str::ulid();
    $updateCorrelationId = (string) Str::ulid();

    $created = $this->actingAs($actor)->postJson(route('api.v1.human-resource.employees.store'), [
        'employee_no' => 'HR-AUD-001',
        'name' => 'Audit Employee',
        'employment_type' => 'teacher',
        'position' => 'Guru Audit',
        'status' => 'active',
        'joined_on' => '2026-08-01',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $createCorrelationId,
    ])->assertCreated();

    $employeeId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.human-resource.employees.update', $employeeId), [
        'name' => 'Audit Employee Baru',
        'position' => 'Guru Senior',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $updateCorrelationId,
    ])->assertOk();

    $createAudit = AuditRecord::query()
        ->where('action', 'human_resource.employee.created')
        ->firstOrFail();
    $updateAudit = AuditRecord::query()
        ->where('action', 'human_resource.employee.updated')
        ->firstOrFail();

    expect(AuditRecord::query()->whereIn('action', [
        'human_resource.employee.created',
        'human_resource.employee.updated',
    ])->count())->toBe(2)
        ->and($createAudit->module)->toBe('HumanResource')
        ->and($createAudit->actor_id)->toBe($actor->id)
        ->and($createAudit->subject_type)->toBe('employee')
        ->and($createAudit->subject_id)->toBe($employeeId)
        ->and($createAudit->correlation_id)->toBe($createCorrelationId)
        ->and($createAudit->metadata)->toMatchArray([
            'changed_fields' => [
                'primary_unit_id',
                'employee_no',
                'name',
                'preferred_name',
                'employment_type',
                'position',
                'status',
                'joined_on',
                'left_on',
                'notes',
            ],
            'result' => [
                'employee_no' => 'HR-AUD-001',
                'name' => 'Audit Employee',
                'employment_type' => 'teacher',
                'position' => 'Guru Audit',
                'status' => 'active',
                'primary_unit_id' => null,
                'joined_on' => '2026-08-01',
                'left_on' => null,
            ],
        ])
        ->and($updateAudit->module)->toBe('HumanResource')
        ->and($updateAudit->actor_id)->toBe($actor->id)
        ->and($updateAudit->subject_type)->toBe('employee')
        ->and($updateAudit->subject_id)->toBe($employeeId)
        ->and($updateAudit->correlation_id)->toBe($updateCorrelationId)
        ->and($updateAudit->metadata)->toMatchArray([
            'changed_fields' => ['name', 'position'],
            'to_status' => 'active',
            'result' => [
                'employee_no' => 'HR-AUD-001',
                'name' => 'Audit Employee Baru',
                'employment_type' => 'teacher',
                'position' => 'Guru Senior',
                'status' => 'active',
                'primary_unit_id' => null,
                'joined_on' => '2026-08-01',
                'left_on' => null,
            ],
        ])
        ->and(array_keys($createAudit->metadata))->not->toContain('password')
        ->and(array_keys($updateAudit->metadata))->not->toContain('password');
});

it('mencatat audit activate dan deactivate employee dengan metadata aman', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $activateCorrelationId = (string) Str::ulid();
    $deactivateCorrelationId = (string) Str::ulid();
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-AUD-002',
        'name' => 'Lifecycle Audit Employee',
        'employment_type' => 'staff',
        'status' => 'inactive',
        'left_on' => '2026-08-01',
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.human-resource.employees.activate', $employee->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $activateCorrelationId,
    ])->assertOk();

    $this->actingAs($actor)->patchJson(route('api.v1.human-resource.employees.deactivate', $employee->id), [
        'left_on' => '2026-08-20',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $deactivateCorrelationId,
    ])->assertOk();

    $activateAudit = AuditRecord::query()
        ->where('action', 'human_resource.employee.activated')
        ->firstOrFail();
    $deactivateAudit = AuditRecord::query()
        ->where('action', 'human_resource.employee.deactivated')
        ->firstOrFail();

    expect(AuditRecord::query()->whereIn('action', [
        'human_resource.employee.activated',
        'human_resource.employee.deactivated',
    ])->count())->toBe(2)
        ->and($activateAudit->module)->toBe('HumanResource')
        ->and($activateAudit->actor_id)->toBe($actor->id)
        ->and($activateAudit->subject_type)->toBe('employee')
        ->and($activateAudit->subject_id)->toBe($employee->id)
        ->and($activateAudit->correlation_id)->toBe($activateCorrelationId)
        ->and($activateAudit->metadata)->toMatchArray([
            'changed_fields' => ['status', 'left_on'],
            'to_status' => 'active',
            'result' => [
                'employee_no' => 'HR-AUD-002',
                'name' => 'Lifecycle Audit Employee',
                'employment_type' => 'staff',
                'position' => null,
                'status' => 'active',
                'primary_unit_id' => null,
                'joined_on' => null,
                'left_on' => null,
            ],
        ])
        ->and($deactivateAudit->module)->toBe('HumanResource')
        ->and($deactivateAudit->actor_id)->toBe($actor->id)
        ->and($deactivateAudit->subject_id)->toBe($employee->id)
        ->and($deactivateAudit->correlation_id)->toBe($deactivateCorrelationId)
        ->and($deactivateAudit->metadata)->toMatchArray([
            'changed_fields' => ['status', 'left_on'],
            'to_status' => 'inactive',
            'result' => [
                'employee_no' => 'HR-AUD-002',
                'name' => 'Lifecycle Audit Employee',
                'employment_type' => 'staff',
                'position' => null,
                'status' => 'inactive',
                'primary_unit_id' => null,
                'joined_on' => null,
                'left_on' => '2026-08-20',
            ],
        ])
        ->and(array_keys($activateAudit->metadata))->not->toContain('password')
        ->and(array_keys($deactivateAudit->metadata))->not->toContain('password');
});

it('mencatat audit assignment employee ke unit dengan metadata aman', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $correlationId = (string) Str::ulid();
    $unitId = createHumanResourceAuditOrganizationUnit('AUD-UNIT', 'Unit Audit HR');
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-AUD-003',
        'name' => 'Assignment Audit Employee',
        'employment_type' => 'musyrif',
        'status' => 'active',
    ]);

    $created = $this->actingAs($actor)->postJson(
        route('api.v1.human-resource.employees.unit-assignments.store', $employee->id),
        [
            'organization_unit_id' => $unitId,
            'role' => 'musyrif',
            'starts_on' => '2026-08-01',
            'is_primary' => true,
        ],
        [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ],
    )
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Assignment employee berhasil dibuat.')
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.organization_unit_id', $unitId)
        ->assertJsonPath('data.role', 'musyrif')
        ->assertJsonPath('data.is_primary', true);

    $assignmentId = (string) $created->json('data.id');
    $createdAt = (string) $created->json('data.created_at');
    $updatedAt = (string) $created->json('data.updated_at');
    $audit = AuditRecord::query()
        ->where('action', 'human_resource.employee.assigned_to_unit')
        ->firstOrFail();

    expect($audit->module)->toBe('HumanResource')
        ->and($audit->actor_id)->toBe($actor->id)
        ->and($audit->subject_type)->toBe('employee_unit_assignment')
        ->and($audit->subject_id)->toBe($assignmentId)
        ->and($audit->correlation_id)->toBe($correlationId)
        ->and($audit->metadata)->toMatchArray([
            'changed_fields' => [
                'employee_id',
                'organization_unit_id',
                'role',
                'starts_on',
                'ends_on',
                'is_primary',
            ],
            'result' => [
                'id' => $assignmentId,
                'employee_id' => $employee->id,
                'organization_unit_id' => $unitId,
                'role' => 'musyrif',
                'starts_on' => '2026-08-01',
                'ends_on' => null,
                'is_primary' => true,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ],
        ])
        ->and(array_keys($audit->metadata))->not->toContain('password');
});

it('menolak assignment employee invalid dengan envelope canonical', function (): void {
    $manage = Permission::create(['name' => 'human_resource.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $employee = EmployeeRecord::query()->create([
        'employee_no' => 'HR-AUD-004',
        'name' => 'Invalid Assignment Employee',
        'employment_type' => 'staff',
        'status' => 'active',
    ]);

    $this->actingAs($actor)->postJson(
        route('api.v1.human-resource.employees.unit-assignments.store', $employee->id),
        [
            'organization_unit_id' => (string) Str::ulid(),
            'role' => 'unknown',
            'starts_on' => '2026-08-20',
            'ends_on' => '2026-08-01',
        ],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['organization_unit_id', 'role', 'ends_on']]);

    $this->actingAs($actor)->postJson(
        route('api.v1.human-resource.employees.unit-assignments.store', (string) Str::ulid()),
        [
            'organization_unit_id' => createHumanResourceAuditOrganizationUnit('AUD-MISS', 'Missing Employee Unit'),
            'role' => 'staff',
        ],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertNotFound()
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

function createHumanResourceAuditOrganizationUnit(string $code, string $name): string
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
