<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the Asrama foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('dormitories'))->toBeTrue()
        ->and(Schema::hasColumns('dormitories', [
            'id',
            'unit_id',
            'code',
            'name',
            'gender_policy',
            'description',
            'status',
            'archived_at',
            'archived_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('dormitory_rooms'))->toBeTrue()
        ->and(Schema::hasColumns('dormitory_rooms', [
            'id',
            'dormitory_id',
            'code',
            'name',
            'capacity',
            'status',
            'archived_at',
            'archived_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_room_placements'))->toBeTrue()
        ->and(Schema::hasColumns('student_room_placements', [
            'id',
            'student_id',
            'dormitory_room_id',
            'student_no',
            'started_at',
            'ended_at',
            'status',
            'reason',
            'active_student_key',
            'created_by',
            'ended_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('dormitory_supervisor_assignments'))->toBeTrue()
        ->and(Schema::hasColumns('dormitory_supervisor_assignments', [
            'id',
            'employee_id',
            'dormitory_id',
            'dormitory_room_id',
            'employee_name',
            'role',
            'started_at',
            'ended_at',
            'status',
            'reason',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('enforces unique dormitory and room codes in their scope', function (): void {
    seedAsramaReferences();
    seedAsramaStructure();

    expect(fn () => DB::table('dormitories')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AA3',
        'unit_id' => '01K41KRG60H6GTYB56B6T42AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra Duplikat',
        'gender_policy' => 'male',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('dormitory_rooms')->insert([
            'id' => '01K41KRG60H6GTYB56B6T42AR3',
            'dormitory_id' => '01K41KRG60H6GTYB56B6T42AA1',
            'code' => 'A-01',
            'name' => 'Kamar A-01 Duplikat',
            'capacity' => 8,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
});

it('guards one active room placement per student', function (): void {
    seedAsramaReferences();
    seedAsramaStructure();

    DB::table('student_room_placements')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AP1',
        'student_id' => '01K41KRG60H6GTYB56B6T42AD1',
        'dormitory_room_id' => '01K41KRG60H6GTYB56B6T42AR1',
        'student_no' => 'NIS-0001',
        'started_at' => '2026-07-01 00:00:00',
        'status' => 'active',
        'active_student_key' => '01K41KRG60H6GTYB56B6T42AD1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('student_room_placements')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AP2',
        'student_id' => '01K41KRG60H6GTYB56B6T42AD1',
        'dormitory_room_id' => '01K41KRG60H6GTYB56B6T42AR2',
        'student_no' => 'NIS-0001',
        'started_at' => '2026-07-02 00:00:00',
        'status' => 'active',
        'active_student_key' => '01K41KRG60H6GTYB56B6T42AD1',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('keeps historical room placement rows after an active placement is closed', function (): void {
    seedAsramaReferences();
    seedAsramaStructure();

    DB::table('student_room_placements')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T42AP3',
            'student_id' => '01K41KRG60H6GTYB56B6T42AD1',
            'dormitory_room_id' => '01K41KRG60H6GTYB56B6T42AR1',
            'student_no' => 'NIS-0001',
            'started_at' => '2026-07-01 00:00:00',
            'ended_at' => '2026-08-01 00:00:00',
            'status' => 'transferred',
            'reason' => 'Pindah kamar',
            'active_student_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T42AP4',
            'student_id' => '01K41KRG60H6GTYB56B6T42AD1',
            'dormitory_room_id' => '01K41KRG60H6GTYB56B6T42AR2',
            'student_no' => 'NIS-0001',
            'started_at' => '2026-08-01 00:00:00',
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
            'active_student_key' => '01K41KRG60H6GTYB56B6T42AD1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('student_room_placements')->where('student_id', '01K41KRG60H6GTYB56B6T42AD1')->count())->toBe(2)
        ->and(DB::table('student_room_placements')->whereNotNull('active_student_key')->count())->toBe(1);
});

it('creates dormitory records through local factories', function (): void {
    seedAsramaReferences();

    $dormitory = DormitoryRecord::factory()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T42AB1',
    ]);
    $room = DormitoryRoomRecord::factory()->create([
        'dormitory_id' => $dormitory->id,
        'capacity' => 12,
    ]);

    expect($dormitory->getKey())->toBeString()
        ->and($room->dormitory->is($dormitory))->toBeTrue()
        ->and($room->capacity)->toBe(12);
});

function seedAsramaReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AB1',
        'code' => 'ASR',
        'name' => 'Asrama Pondok',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AD1',
        'student_no' => 'NIS-0001',
        'full_name' => 'Ahmad Fikri',
        'gender' => 'male',
        'primary_unit_id' => '01K41KRG60H6GTYB56B6T42AB1',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        'id' => '01K41KRG60H6GTYB56B6T42AE1',
        'employee_no' => 'PEG-0001',
        'name' => 'Ustadz Hasan',
        'employment_type' => 'teacher',
        'position' => 'Musyrif',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedAsramaStructure(): void
{
    DB::table('dormitories')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T42AA1',
            'unit_id' => '01K41KRG60H6GTYB56B6T42AB1',
            'code' => 'ASR-PTR',
            'name' => 'Asrama Putra',
            'gender_policy' => 'male',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T42AA2',
            'unit_id' => '01K41KRG60H6GTYB56B6T42AB1',
            'code' => 'ASR-PTS',
            'name' => 'Asrama Putri',
            'gender_policy' => 'female',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('dormitory_rooms')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T42AR1',
            'dormitory_id' => '01K41KRG60H6GTYB56B6T42AA1',
            'code' => 'A-01',
            'name' => 'Kamar A-01',
            'capacity' => 8,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T42AR2',
            'dormitory_id' => '01K41KRG60H6GTYB56B6T42AA1',
            'code' => 'A-02',
            'name' => 'Kamar A-02',
            'capacity' => 8,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
