<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the KelasRombel foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('academic_curricula'))->toBeTrue()
        ->and(Schema::hasColumns('academic_curricula', [
            'id',
            'code',
            'name',
            'description',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('class_levels'))->toBeTrue()
        ->and(Schema::hasColumns('class_levels', [
            'id',
            'unit_id',
            'code',
            'name',
            'sequence',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('class_groups'))->toBeTrue()
        ->and(Schema::hasColumns('class_groups', [
            'id',
            'academic_year_id',
            'academic_term_id',
            'unit_id',
            'curriculum_id',
            'class_level_id',
            'code',
            'name',
            'capacity',
            'status',
            'archived_at',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('class_group_students'))->toBeTrue()
        ->and(Schema::hasColumns('class_group_students', [
            'id',
            'class_group_id',
            'academic_term_id',
            'student_id',
            'student_no',
            'joined_on',
            'left_on',
            'status',
            'reason',
            'active_period_student_key',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('class_group_homerooms'))->toBeTrue()
        ->and(Schema::hasColumns('class_group_homerooms', [
            'id',
            'class_group_id',
            'employee_id',
            'employee_name',
            'assigned_on',
            'ended_on',
            'status',
            'reason',
            'active_class_group_key',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('enforces unique academic structure codes in their scope', function (): void {
    seedKelasRombelReferences();

    DB::table('academic_curricula')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AA1',
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_levels')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AA2',
        'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AA3',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T32AC1',
        'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
        'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
        'curriculum_id' => '01K41KRG60H6GTYB56B6T32AA1',
        'class_level_id' => '01K41KRG60H6GTYB56B6T32AA2',
        'code' => 'VII-A',
        'name' => 'VII A',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('academic_curricula')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AA4',
        'code' => 'KUR-2026',
        'name' => 'Kurikulum Duplikat',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('class_levels')->insert([
            'id' => '01K41KRG60H6GTYB56B6T32AA5',
            'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
            'code' => 'VII',
            'name' => 'Kelas VII Duplikat',
            'sequence' => 8,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('class_groups')->insert([
            'id' => '01K41KRG60H6GTYB56B6T32AA6',
            'academic_year_id' => '01K41KRG60H6GTYB56B6T32AC1',
            'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
            'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
            'class_level_id' => '01K41KRG60H6GTYB56B6T32AA2',
            'code' => 'VII-A',
            'name' => 'VII A Duplikat',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
});

it('guards one active student placement per academic term', function (): void {
    seedKelasRombelReferences();
    seedKelasRombelStructure();

    DB::table('class_group_students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32BA1',
        'class_group_id' => '01K41KRG60H6GTYB56B6T32AG1',
        'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
        'student_id' => '01K41KRG60H6GTYB56B6T32AD1',
        'student_no' => 'NIS-0001',
        'joined_on' => '2026-07-01',
        'status' => 'active',
        'active_period_student_key' => '01K41KRG60H6GTYB56B6T32AC2:01K41KRG60H6GTYB56B6T32AD1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('class_group_students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32BA2',
        'class_group_id' => '01K41KRG60H6GTYB56B6T32AG2',
        'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
        'student_id' => '01K41KRG60H6GTYB56B6T32AD1',
        'student_no' => 'NIS-0001',
        'joined_on' => '2026-07-02',
        'status' => 'active',
        'active_period_student_key' => '01K41KRG60H6GTYB56B6T32AC2:01K41KRG60H6GTYB56B6T32AD1',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('guards one active homeroom teacher per class group', function (): void {
    seedKelasRombelReferences();
    seedKelasRombelStructure();

    DB::table('class_group_homerooms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32CA1',
        'class_group_id' => '01K41KRG60H6GTYB56B6T32AG1',
        'employee_id' => '01K41KRG60H6GTYB56B6T32AE1',
        'employee_name' => 'Ustadz Hasan',
        'assigned_on' => '2026-07-01',
        'status' => 'active',
        'active_class_group_key' => '01K41KRG60H6GTYB56B6T32AG1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('class_group_homerooms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32CA2',
        'class_group_id' => '01K41KRG60H6GTYB56B6T32AG1',
        'employee_id' => '01K41KRG60H6GTYB56B6T32AE2',
        'employee_name' => 'Ustadz Ahmad',
        'assigned_on' => '2026-07-02',
        'status' => 'active',
        'active_class_group_key' => '01K41KRG60H6GTYB56B6T32AG1',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

function seedKelasRombelReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AB1',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AC1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AC2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T32AC1',
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

    DB::table('students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AD1',
        'student_no' => 'NIS-0001',
        'full_name' => 'Ahmad Fikri',
        'primary_unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T32AE1',
            'employee_no' => 'PEG-0001',
            'name' => 'Ustadz Hasan',
            'employment_type' => 'teacher',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T32AE2',
            'employee_no' => 'PEG-0002',
            'name' => 'Ustadz Ahmad',
            'employment_type' => 'teacher',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}

function seedKelasRombelStructure(): void
{
    DB::table('academic_curricula')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AF1',
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_levels')->insert([
        'id' => '01K41KRG60H6GTYB56B6T32AF2',
        'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T32AG1',
            'academic_year_id' => '01K41KRG60H6GTYB56B6T32AC1',
            'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
            'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
            'curriculum_id' => '01K41KRG60H6GTYB56B6T32AF1',
            'class_level_id' => '01K41KRG60H6GTYB56B6T32AF2',
            'code' => 'VII-A',
            'name' => 'VII A',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T32AG2',
            'academic_year_id' => '01K41KRG60H6GTYB56B6T32AC1',
            'academic_term_id' => '01K41KRG60H6GTYB56B6T32AC2',
            'unit_id' => '01K41KRG60H6GTYB56B6T32AB1',
            'curriculum_id' => '01K41KRG60H6GTYB56B6T32AF1',
            'class_level_id' => '01K41KRG60H6GTYB56B6T32AF2',
            'code' => 'VII-B',
            'name' => 'VII B',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
