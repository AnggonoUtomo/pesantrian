<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitorySupervisorAssignmentRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\StudentRoomPlacementRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Facades\DB;

it('mengembalikan list asrama dengan filter pagination sort dan envelope canonical', function (): void {
    $view = Permission::create(['name' => 'asrama.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    seedAsramaReadReferences();
    $target = DormitoryRecord::query()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra',
        'gender_policy' => 'male',
        'description' => 'Area santri putra',
        'status' => 'active',
    ]);
    $room = DormitoryRoomRecord::query()->create([
        'dormitory_id' => $target->id,
        'code' => 'A-01',
        'name' => 'Kamar A-01',
        'capacity' => 8,
        'status' => 'active',
    ]);
    DormitoryRecord::query()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
        'code' => 'ASR-PTS',
        'name' => 'Asrama Putri',
        'gender_policy' => 'female',
        'status' => 'inactive',
    ]);
    StudentRoomPlacementRecord::query()->create([
        'student_id' => '01K41KRG60H6GTYB56B6T52AD1',
        'dormitory_room_id' => $room->id,
        'student_no' => 'NIS-0001',
        'started_at' => '2026-07-01 00:00:00',
        'status' => 'active',
        'active_student_key' => '01K41KRG60H6GTYB56B6T52AD1',
    ]);

    $query = http_build_query([
        'search' => 'Putra',
        'filter' => [
            'unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
            'gender_policy' => 'male',
            'status' => 'active',
        ],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'code',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.pesantrian.asrama.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar asrama berhasil dibaca.')
        ->assertJsonPath('data.0.id', $target->id)
        ->assertJsonPath('data.0.code', 'ASR-PTR')
        ->assertJsonPath('data.0.name', 'Asrama Putra')
        ->assertJsonPath('data.0.gender_policy', 'male')
        ->assertJsonPath('data.0.unit.name', 'Asrama Pondok')
        ->assertJsonPath('data.0.room_count', 1)
        ->assertJsonPath('data.0.capacity', 8)
        ->assertJsonPath('data.0.occupied_count', 1)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'unit',
                'code',
                'name',
                'gender_policy',
                'description',
                'room_count',
                'capacity',
                'occupied_count',
                'available_capacity',
                'status',
                'archived_at',
                'created_at',
                'updated_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('mengembalikan detail asrama dengan kamar placement aktif dan musyrif aktif', function (): void {
    $view = Permission::create(['name' => 'asrama.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    seedAsramaReadReferences();
    $dormitory = DormitoryRecord::query()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra',
        'gender_policy' => 'male',
        'status' => 'active',
    ]);
    $room = DormitoryRoomRecord::query()->create([
        'dormitory_id' => $dormitory->id,
        'code' => 'A-01',
        'name' => 'Kamar A-01',
        'capacity' => 8,
        'status' => 'active',
    ]);
    StudentRoomPlacementRecord::query()->create([
        'student_id' => '01K41KRG60H6GTYB56B6T52AD1',
        'dormitory_room_id' => $room->id,
        'student_no' => 'NIS-0001',
        'started_at' => '2026-07-01 00:00:00',
        'status' => 'active',
        'active_student_key' => '01K41KRG60H6GTYB56B6T52AD1',
    ]);
    DormitorySupervisorAssignmentRecord::query()->create([
        'employee_id' => '01K41KRG60H6GTYB56B6T52AE1',
        'dormitory_id' => $dormitory->id,
        'employee_name' => 'Ustadz Hasan',
        'role' => 'musyrif',
        'started_at' => '2026-07-01 00:00:00',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.pesantrian.asrama.show', $dormitory->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Detail asrama berhasil dibaca.')
        ->assertJsonPath('data.id', $dormitory->id)
        ->assertJsonPath('data.rooms.0.id', $room->id)
        ->assertJsonPath('data.rooms.0.occupied_count', 1)
        ->assertJsonPath('data.rooms.0.available_capacity', 7)
        ->assertJsonPath('data.placements.0.student_id', '01K41KRG60H6GTYB56B6T52AD1')
        ->assertJsonPath('data.placements.0.student_name', 'Ahmad Fikri')
        ->assertJsonPath('data.supervisors.0.employee_id', '01K41KRG60H6GTYB56B6T52AE1')
        ->assertJsonPath('data.supervisors.0.employee_name', 'Ustadz Hasan')
        ->assertJsonStructure([
            'data' => [
                'rooms' => [[
                    'id',
                    'code',
                    'name',
                    'capacity',
                    'occupied_count',
                    'available_capacity',
                    'status',
                    'archived_at',
                ]],
                'placements' => [[
                    'id',
                    'student_id',
                    'student_no',
                    'student_name',
                    'room_id',
                    'room_code',
                    'started_at',
                    'ended_at',
                    'status',
                    'reason',
                ]],
                'supervisors' => [[
                    'id',
                    'employee_id',
                    'employee_name',
                    'role',
                    'dormitory_id',
                    'dormitory_room_id',
                    'room_code',
                    'started_at',
                    'ended_at',
                    'status',
                    'reason',
                ]],
            ],
        ]);
});

it('menolak actor tanpa permission asrama view', function (): void {
    $actor = User::factory()->create();

    seedAsramaReadReferences();
    $dormitory = DormitoryRecord::query()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra',
        'gender_policy' => 'male',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.pesantrian.asrama.index'))
        ->assertForbidden();

    $this->actingAs($actor)
        ->getJson(route('api.v1.pesantrian.asrama.show', $dormitory->id))
        ->assertForbidden();
});

function seedAsramaReadReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T52AB1',
        'code' => 'ASR',
        'name' => 'Asrama Pondok',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T52AD1',
        'student_no' => 'NIS-0001',
        'full_name' => 'Ahmad Fikri',
        'gender' => 'male',
        'primary_unit_id' => '01K41KRG60H6GTYB56B6T52AB1',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        'id' => '01K41KRG60H6GTYB56B6T52AE1',
        'employee_no' => 'PEG-0001',
        'name' => 'Ustadz Hasan',
        'employment_type' => 'teacher',
        'position' => 'Musyrif',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
