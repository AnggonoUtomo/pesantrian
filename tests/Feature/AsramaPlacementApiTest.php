<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\StudentRoomPlacementRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('menempatkan santri aktif ke kamar asrama melalui API terotorisasi dengan audit', function (): void {
    [$actor, $dormitory, $room, $student] = makeAsramaPlacementScenario();

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.placements.store', $dormitory->id), [
        'student_id' => $student->id,
        'dormitory_room_id' => $room->id,
        'started_at' => '2026-07-15 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Santri berhasil ditempatkan ke kamar asrama.')
        ->assertJsonPath('data.student_id', $student->id)
        ->assertJsonPath('data.student_no', 'NIS-ASR-001')
        ->assertJsonPath('data.room_id', $room->id)
        ->assertJsonPath('data.room_code', 'A-01')
        ->assertJsonPath('data.status', 'active');

    expect(StudentRoomPlacementRecord::query()
        ->where('student_id', $student->id)
        ->where('dormitory_room_id', $room->id)
        ->where('active_student_key', $student->id)
        ->exists())->toBeTrue()
        ->and(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.student.placed');
});

it('memindahkan santri ke kamar lain dan menutup penempatan lama', function (): void {
    [$actor, $dormitory, $sourceRoom, $student] = makeAsramaPlacementScenario();
    $targetRoom = DormitoryRoomRecord::factory()->create([
        'dormitory_id' => $dormitory->id,
        'code' => 'A-02',
        'name' => 'Kamar A-02',
        'capacity' => 2,
    ]);
    $placement = StudentRoomPlacementRecord::factory()->create([
        'student_id' => $student->id,
        'dormitory_room_id' => $sourceRoom->id,
        'student_no' => $student->student_no,
        'started_at' => '2026-07-15 08:00:00',
        'active_student_key' => $student->id,
        'created_by' => $actor->id,
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.placements.transfer', [$dormitory->id, $placement->id]), [
        'target_room_id' => $targetRoom->id,
        'started_at' => '2026-08-01 08:00:00',
        'reason' => 'Pindah kamar agar satu kelompok belajar.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Santri berhasil dipindahkan ke kamar asrama baru.')
        ->assertJsonPath('data.previous.id', $placement->id)
        ->assertJsonPath('data.previous.status', 'moved')
        ->assertJsonPath('data.current.room_id', $targetRoom->id)
        ->assertJsonPath('data.current.status', 'active');

    $placement->refresh();

    expect($placement->status)->toBe('moved')
        ->and($placement->active_student_key)->toBeNull()
        ->and(StudentRoomPlacementRecord::query()
            ->where('student_id', $student->id)
            ->where('dormitory_room_id', $targetRoom->id)
            ->where('active_student_key', $student->id)
            ->exists())->toBeTrue()
        ->and(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.student.transferred');
});

it('mengeluarkan santri dari kamar asrama dan menutup penempatan aktif', function (): void {
    [$actor, $dormitory, $room, $student] = makeAsramaPlacementScenario();
    $placement = StudentRoomPlacementRecord::factory()->create([
        'student_id' => $student->id,
        'dormitory_room_id' => $room->id,
        'student_no' => $student->student_no,
        'started_at' => '2026-07-15 08:00:00',
        'active_student_key' => $student->id,
        'created_by' => $actor->id,
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.placements.remove', [$dormitory->id, $placement->id]), [
        'ended_at' => '2026-08-10 17:00:00',
        'reason' => 'Pulang bersama wali.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Santri berhasil dikeluarkan dari kamar asrama.')
        ->assertJsonPath('data.id', $placement->id)
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.reason', 'Pulang bersama wali.');

    $placement->refresh();

    expect($placement->status)->toBe('inactive')
        ->and($placement->active_student_key)->toBeNull()
        ->and($placement->ended_by)->toBe($actor->id)
        ->and(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.student.removed');
});

it('menolak penempatan ketika kamar penuh atau kebijakan gender tidak sesuai', function (): void {
    [$actor, $dormitory, $room, $student] = makeAsramaPlacementScenario();
    $otherStudent = StudentRecord::factory()->create([
        'student_no' => 'NIS-ASR-002',
        'full_name' => 'Ahmad Kamar Penuh',
        'gender' => 'male',
        'primary_unit_id' => $dormitory->unit_id,
        'status' => 'active',
    ]);
    StudentRoomPlacementRecord::factory()->create([
        'student_id' => $otherStudent->id,
        'dormitory_room_id' => $room->id,
        'student_no' => $otherStudent->student_no,
        'active_student_key' => $otherStudent->id,
    ]);

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.placements.store', $dormitory->id), [
        'student_id' => $student->id,
        'dormitory_room_id' => $room->id,
        'started_at' => '2026-07-15 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'ASRAMA_PLACEMENT_INVALID')
        ->assertJsonValidationErrors(['dormitory_room_id']);

    $femaleStudent = StudentRecord::factory()->create([
        'student_no' => 'NIS-ASR-003',
        'full_name' => 'Aisyah Tidak Cocok',
        'gender' => 'female',
        'primary_unit_id' => $dormitory->unit_id,
        'status' => 'active',
    ]);
    $availableRoom = DormitoryRoomRecord::factory()->create([
        'dormitory_id' => $dormitory->id,
        'code' => 'A-03',
        'name' => 'Kamar A-03',
        'capacity' => 2,
    ]);

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.placements.store', $dormitory->id), [
        'student_id' => $femaleStudent->id,
        'dormitory_room_id' => $availableRoom->id,
        'started_at' => '2026-07-15 08:00:00',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'ASRAMA_PLACEMENT_INVALID')
        ->assertJsonValidationErrors(['student_id']);
});

/**
 * @return array{0: User, 1: DormitoryRecord, 2: DormitoryRoomRecord, 3: StudentRecord}
 */
function makeAsramaPlacementScenario(): array
{
    $placement = Permission::firstOrCreate(['name' => 'asrama.placement', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($placement);

    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T62AC1',
        'code' => 'ASR-PLC',
        'name' => 'Asrama Placement',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $dormitory = DormitoryRecord::factory()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T62AC1',
        'code' => 'ASR-PLC',
        'name' => 'Asrama Placement',
        'gender_policy' => 'male',
        'status' => 'active',
    ]);
    $room = DormitoryRoomRecord::factory()->create([
        'dormitory_id' => $dormitory->id,
        'code' => 'A-01',
        'name' => 'Kamar A-01',
        'capacity' => 1,
        'status' => 'active',
    ]);
    $student = StudentRecord::factory()->create([
        'student_no' => 'NIS-ASR-001',
        'full_name' => 'Ahmad Asrama',
        'gender' => 'male',
        'primary_unit_id' => $dormitory->unit_id,
        'status' => 'active',
    ]);

    return [$actor, $dormitory, $room, $student];
}
