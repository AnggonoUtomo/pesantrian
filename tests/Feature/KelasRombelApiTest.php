<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupHomeroomRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupStudentRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Facades\DB;

it('mengembalikan list rombel dengan filter pagination sort dan envelope canonical', function (): void {
    $view = Permission::create(['name' => 'kelas_rombel.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    $references = seedKelasRombelReadReferences();
    $curriculum = AcademicCurriculumRecord::query()->create([
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
    ]);
    $level = ClassLevelRecord::query()->create([
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
    ]);
    $target = ClassGroupRecord::query()->create([
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => 'VII-A',
        'name' => 'VII A Putra',
        'capacity' => 32,
        'status' => 'active',
    ]);
    ClassGroupRecord::query()->create([
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => 'VII-B',
        'name' => 'VII B Putri',
        'capacity' => 30,
        'status' => 'draft',
    ]);

    $query = http_build_query([
        'search' => 'Putra',
        'filter' => [
            'academic_year_id' => $references['year_id'],
            'academic_term_id' => $references['term_id'],
            'unit_id' => $references['unit_id'],
            'curriculum_id' => $curriculum->id,
            'status' => 'active',
        ],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'code',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.class-groups.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar rombel berhasil dibaca.')
        ->assertJsonPath('data.0.id', $target->id)
        ->assertJsonPath('data.0.code', 'VII-A')
        ->assertJsonPath('data.0.name', 'VII A Putra')
        ->assertJsonPath('data.0.capacity', 32)
        ->assertJsonPath('data.0.status', 'active')
        ->assertJsonPath('data.0.academic_year.name', 'Tahun Ajaran 2026/2027')
        ->assertJsonPath('data.0.academic_term.name', 'Semester Ganjil')
        ->assertJsonPath('data.0.unit.name', 'MTs Saka')
        ->assertJsonPath('data.0.curriculum.name', 'Kurikulum 2026')
        ->assertJsonPath('data.0.class_level.name', 'Kelas VII')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'academic_year',
                'academic_term',
                'unit',
                'curriculum',
                'class_level',
                'code',
                'name',
                'capacity',
                'status',
                'archived_at',
                'created_at',
                'updated_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('mengembalikan detail rombel dengan daftar santri dan wali kelas', function (): void {
    $view = Permission::create(['name' => 'kelas_rombel.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    $references = seedKelasRombelReadReferences();
    $curriculum = AcademicCurriculumRecord::query()->create([
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
    ]);
    $level = ClassLevelRecord::query()->create([
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
    ]);
    $classGroup = ClassGroupRecord::query()->create([
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => 'VII-A',
        'name' => 'VII A',
        'capacity' => 32,
        'status' => 'active',
    ]);

    DB::table('students')->insert([
        'id' => '01K41KRG60H6GTYB56B6T33AD1',
        'student_no' => 'NIS-0001',
        'full_name' => 'Ahmad Fikri',
        'primary_unit_id' => $references['unit_id'],
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('employees')->insert([
        'id' => '01K41KRG60H6GTYB56B6T33AE1',
        'employee_no' => 'PEG-0001',
        'name' => 'Ustadz Hasan',
        'employment_type' => 'teacher',
        'position' => 'Wali Kelas',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    ClassGroupStudentRecord::query()->create([
        'class_group_id' => $classGroup->id,
        'academic_term_id' => $references['term_id'],
        'student_id' => '01K41KRG60H6GTYB56B6T33AD1',
        'student_no' => 'NIS-0001',
        'joined_on' => '2026-07-15',
        'status' => 'active',
        'active_period_student_key' => $references['term_id'].':01K41KRG60H6GTYB56B6T33AD1',
    ]);
    ClassGroupHomeroomRecord::query()->create([
        'class_group_id' => $classGroup->id,
        'employee_id' => '01K41KRG60H6GTYB56B6T33AE1',
        'employee_name' => 'Ustadz Hasan',
        'assigned_on' => '2026-07-01',
        'status' => 'active',
        'active_class_group_key' => $classGroup->id,
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.class-groups.show', $classGroup->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Detail rombel berhasil dibaca.')
        ->assertJsonPath('data.id', $classGroup->id)
        ->assertJsonPath('data.students.0.student_id', '01K41KRG60H6GTYB56B6T33AD1')
        ->assertJsonPath('data.students.0.student_no', 'NIS-0001')
        ->assertJsonPath('data.students.0.student_name', 'Ahmad Fikri')
        ->assertJsonPath('data.homerooms.0.employee_id', '01K41KRG60H6GTYB56B6T33AE1')
        ->assertJsonPath('data.homerooms.0.employee_name', 'Ustadz Hasan')
        ->assertJsonStructure([
            'data' => [
                'students' => [[
                    'id',
                    'student_id',
                    'student_no',
                    'student_name',
                    'joined_on',
                    'left_on',
                    'status',
                    'reason',
                ]],
                'homerooms' => [[
                    'id',
                    'employee_id',
                    'employee_name',
                    'assigned_on',
                    'ended_on',
                    'status',
                    'reason',
                ]],
            ],
        ]);
});

it('menolak actor tanpa permission kelas_rombel view', function (): void {
    $actor = User::factory()->create();
    $references = seedKelasRombelReadReferences();
    $curriculum = AcademicCurriculumRecord::query()->create([
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
    ]);
    $level = ClassLevelRecord::query()->create([
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
    ]);
    $classGroup = ClassGroupRecord::query()->create([
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => 'VII-A',
        'name' => 'VII A',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.class-groups.index'))
        ->assertForbidden();

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.class-groups.show', $classGroup->id))
        ->assertForbidden();
});

/** @return array{unit_id: string, year_id: string, term_id: string} */
function seedKelasRombelReadReferences(): array
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T33AB1',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T33AC1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T33AC2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T33AC1',
        'code' => '2026-1',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'unit_id' => '01K41KRG60H6GTYB56B6T33AB1',
        'year_id' => '01K41KRG60H6GTYB56B6T33AC1',
        'term_id' => '01K41KRG60H6GTYB56B6T33AC2',
    ];
}
