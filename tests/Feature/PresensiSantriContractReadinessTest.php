<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Application\Contracts\ActiveClassGroupRosterReader;
use App\Modules\Pesantrian\Asrama\Application\Contracts\ActiveDormitoryResidentReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;

it('can read active students for attendance through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000001',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000002',
            'student_no' => 'NIS-PS-001',
            'full_name' => 'Ahmad Presensi',
            'primary_unit_id' => '01K6ATTSNDRDY000000000001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $student = app(ActiveStudentReader::class)->findActive('01K6ATTSNDRDY000000000002');

    expect($student)->not->toBeNull()
        ->and($student?->studentNo)->toBe('NIS-PS-001')
        ->and($student?->fullName)->toBe('Ahmad Presensi');
});

it('can read active class group roster through the KelasRombel public contract', function (): void {
    seedClassGroupRosterReadinessData();

    $roster = app(ActiveClassGroupRosterReader::class)->studentsForClassGroup('01K6ATTSNDRDY000000000013');

    expect($roster)->toHaveCount(1)
        ->and($roster[0]->studentId)->toBe('01K6ATTSNDRDY000000000016')
        ->and($roster[0]->studentNo)->toBe('NIS-PS-002')
        ->and($roster[0]->studentName)->toBe('Budi Rombel')
        ->and($roster[0]->classGroupId)->toBe('01K6ATTSNDRDY000000000013')
        ->and($roster[0]->academicTermId)->toBe('01K6ATTSNDRDY000000000012');
});

it('can read active dormitory residents through the Asrama public contract', function (): void {
    seedDormitoryResidentReadinessData();

    $residents = app(ActiveDormitoryResidentReader::class)->residentsForDormitory('01K6ATTSNDRDY000000000023');

    expect($residents)->toHaveCount(1)
        ->and($residents[0]->studentId)->toBe('01K6ATTSNDRDY000000000026')
        ->and($residents[0]->studentNo)->toBe('NIS-PS-004')
        ->and($residents[0]->studentName)->toBe('Dewi Asrama')
        ->and($residents[0]->dormitoryId)->toBe('01K6ATTSNDRDY000000000023')
        ->and($residents[0]->roomId)->toBe('01K6ATTSNDRDY000000000024');
});

function seedClassGroupRosterReadinessData(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000010',
        'code' => 'MA',
        'name' => 'MA Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K6ATTSNDRDY000000000011',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K6ATTSNDRDY000000000012',
        'academic_year_id' => '01K6ATTSNDRDY000000000011',
        'code' => '2026-1',
        'name' => 'Semester Ganjil 2026/2027',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_levels')->insert([
        'id' => '01K6ATTSNDRDY000000000014',
        'unit_id' => '01K6ATTSNDRDY000000000010',
        'code' => 'X',
        'name' => 'Kelas X',
        'sequence' => 10,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        'id' => '01K6ATTSNDRDY000000000013',
        'academic_year_id' => '01K6ATTSNDRDY000000000011',
        'academic_term_id' => '01K6ATTSNDRDY000000000012',
        'unit_id' => '01K6ATTSNDRDY000000000010',
        'class_level_id' => '01K6ATTSNDRDY000000000014',
        'code' => 'X-A',
        'name' => 'Kelas X A',
        'capacity' => 30,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000016',
            'student_no' => 'NIS-PS-002',
            'full_name' => 'Budi Rombel',
            'primary_unit_id' => '01K6ATTSNDRDY000000000010',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000017',
            'student_no' => 'NIS-PS-003',
            'full_name' => 'Cici Nonaktif',
            'primary_unit_id' => '01K6ATTSNDRDY000000000010',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('class_group_students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000018',
            'class_group_id' => '01K6ATTSNDRDY000000000013',
            'academic_term_id' => '01K6ATTSNDRDY000000000012',
            'student_id' => '01K6ATTSNDRDY000000000016',
            'student_no' => 'NIS-PS-002',
            'joined_on' => '2026-07-01',
            'left_on' => null,
            'status' => 'active',
            'active_period_student_key' => '01K6ATTSNDRDY000000000012:01K6ATTSNDRDY000000000016',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000019',
            'class_group_id' => '01K6ATTSNDRDY000000000013',
            'academic_term_id' => '01K6ATTSNDRDY000000000012',
            'student_id' => '01K6ATTSNDRDY000000000017',
            'student_no' => 'NIS-PS-003',
            'joined_on' => '2026-07-01',
            'left_on' => '2026-08-01',
            'status' => 'removed',
            'active_period_student_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}

function seedDormitoryResidentReadinessData(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000020',
        'code' => 'ASP',
        'name' => 'Asrama Putri',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('dormitories')->insert([
        'id' => '01K6ATTSNDRDY000000000023',
        'unit_id' => '01K6ATTSNDRDY000000000020',
        'code' => 'ASP-01',
        'name' => 'Asrama Putri 1',
        'gender_policy' => 'female',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('dormitory_rooms')->insert([
        'id' => '01K6ATTSNDRDY000000000024',
        'dormitory_id' => '01K6ATTSNDRDY000000000023',
        'code' => 'A-01',
        'name' => 'Kamar A 01',
        'capacity' => 4,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000026',
            'student_no' => 'NIS-PS-004',
            'full_name' => 'Dewi Asrama',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000027',
            'student_no' => 'NIS-PS-005',
            'full_name' => 'Eka Pindah',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_room_placements')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000028',
            'student_id' => '01K6ATTSNDRDY000000000026',
            'dormitory_room_id' => '01K6ATTSNDRDY000000000024',
            'student_no' => 'NIS-PS-004',
            'started_at' => '2026-07-01 07:00:00',
            'ended_at' => null,
            'status' => 'active',
            'active_student_key' => '01K6ATTSNDRDY000000000026',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000029',
            'student_id' => '01K6ATTSNDRDY000000000027',
            'dormitory_room_id' => '01K6ATTSNDRDY000000000024',
            'student_no' => 'NIS-PS-005',
            'started_at' => '2026-07-01 07:00:00',
            'ended_at' => '2026-08-01 07:00:00',
            'status' => 'moved',
            'active_student_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
