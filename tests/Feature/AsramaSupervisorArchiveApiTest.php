<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('menugaskan dan mengakhiri musyrif asrama melalui API terotorisasi dengan audit', function (): void {
    [$actor, $dormitory, $room, $employee] = makeAsramaSupervisorArchiveScenario(['asrama.supervisor']);

    $assigned = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.supervisors.store', $dormitory->id), [
        'employee_id' => $employee->id,
        'dormitory_room_id' => $room->id,
        'role' => 'musyrif',
        'started_at' => '2026-07-15 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Musyrif asrama berhasil ditugaskan.')
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.employee_name', 'Ustadz Asrama')
        ->assertJsonPath('data.dormitory_id', $dormitory->id)
        ->assertJsonPath('data.dormitory_room_id', $room->id)
        ->assertJsonPath('data.room_code', 'A-01')
        ->assertJsonPath('data.status', 'active');

    $assignmentId = (string) $assigned->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.supervisors.end', [$dormitory->id, $assignmentId]), [
        'ended_at' => '2026-08-01 17:00:00',
        'reason' => 'Rotasi tugas musyrif.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Tugas musyrif asrama berhasil diakhiri.')
        ->assertJsonPath('data.id', $assignmentId)
        ->assertJsonPath('data.status', 'ended')
        ->assertJsonPath('data.reason', 'Rotasi tugas musyrif.');

    expect(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.supervisor.assigned')
        ->toContain('asrama.supervisor.ended');
});

it('mengarsipkan dan memulihkan asrama serta menolak placement saat asrama terarsip', function (): void {
    [$actor, $dormitory, $room] = makeAsramaSupervisorArchiveScenario(['asrama.archive', 'asrama.placement']);
    $student = StudentRecord::factory()->create([
        'student_no' => 'NIS-ARC-001',
        'full_name' => 'Ahmad Arsip',
        'gender' => 'male',
        'primary_unit_id' => $dormitory->unit_id,
        'status' => 'active',
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.archive', $dormitory->id), [
        'reason' => 'Gedung direnovasi.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Asrama berhasil diarsipkan.')
        ->assertJsonPath('data.archived_at', fn (?string $value): bool => $value !== null);

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.placements.store', $dormitory->id), [
        'student_id' => $student->id,
        'dormitory_room_id' => $room->id,
        'started_at' => '2026-08-05 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['dormitory_id']);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.restore', $dormitory->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Asrama berhasil dipulihkan.')
        ->assertJsonPath('data.archived_at', null);

    expect(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.dormitory.archived')
        ->toContain('asrama.dormitory.restored');
});

it('mengarsipkan dan memulihkan kamar serta menolak placement saat kamar terarsip', function (): void {
    [$actor, $dormitory, $room] = makeAsramaSupervisorArchiveScenario(['asrama.archive', 'asrama.placement']);
    $student = StudentRecord::factory()->create([
        'student_no' => 'NIS-ARC-002',
        'full_name' => 'Ahmad Kamar Arsip',
        'gender' => 'male',
        'primary_unit_id' => $dormitory->unit_id,
        'status' => 'active',
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.rooms.archive', [$dormitory->id, $room->id]), [
        'reason' => 'Kamar direnovasi.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Kamar asrama berhasil diarsipkan.')
        ->assertJsonPath('data.rooms.0.archived_at', fn (?string $value): bool => $value !== null);

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.placements.store', $dormitory->id), [
        'student_id' => $student->id,
        'dormitory_room_id' => $room->id,
        'started_at' => '2026-08-05 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['dormitory_room_id']);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.rooms.restore', [$dormitory->id, $room->id]), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Kamar asrama berhasil dipulihkan.')
        ->assertJsonPath('data.rooms.0.archived_at', null);

    expect(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.room.archived')
        ->toContain('asrama.room.restored');
});

it('menolak pegawai tidak aktif saat penugasan musyrif', function (): void {
    [$actor, $dormitory, $room, $employee] = makeAsramaSupervisorArchiveScenario(['asrama.supervisor']);
    $employee->forceFill(['status' => 'inactive'])->save();

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.supervisors.store', $dormitory->id), [
        'employee_id' => $employee->id,
        'dormitory_room_id' => $room->id,
        'role' => 'musyrif',
        'started_at' => '2026-07-15 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'ASRAMA_SUPERVISOR_INVALID')
        ->assertJsonValidationErrors(['employee_id']);
});

/**
 * @param  list<string>  $permissions
 * @return array{0: User, 1: DormitoryRecord, 2: DormitoryRoomRecord, 3: EmployeeRecord}
 */
function makeAsramaSupervisorArchiveScenario(array $permissions): array
{
    $actor = User::factory()->create();

    foreach ($permissions as $permission) {
        $record = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $actor->givePermissionTo($record);
    }

    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T62AD1',
        'code' => 'ASR-SPV',
        'name' => 'Asrama Supervisor',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $dormitory = DormitoryRecord::factory()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T62AD1',
        'code' => 'ASR-SPV',
        'name' => 'Asrama Supervisor',
        'gender_policy' => 'male',
        'status' => 'active',
    ]);
    $room = DormitoryRoomRecord::factory()->create([
        'dormitory_id' => $dormitory->id,
        'code' => 'A-01',
        'name' => 'Kamar A-01',
        'capacity' => 2,
        'status' => 'active',
    ]);
    $employee = EmployeeRecord::query()->create([
        'id' => (string) Str::ulid(),
        'primary_unit_id' => $dormitory->unit_id,
        'employee_no' => 'EMP-ASR-001',
        'name' => 'Ustadz Asrama',
        'preferred_name' => null,
        'employment_type' => 'staff',
        'position' => 'Musyrif',
        'status' => 'active',
        'joined_on' => '2026-07-01',
        'left_on' => null,
        'notes' => null,
    ]);

    return [$actor, $dormitory, $room, $employee];
}
