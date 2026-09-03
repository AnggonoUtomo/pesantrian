<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class KelasRombelPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_menolak_actor_tanpa_permission_kelas_rombel_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('academic.class-groups.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_kelas_rombel(): void
    {
        $view = Permission::create(['name' => 'kelas_rombel.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $fixture = $this->createClassGroupFixture();

        $this->actingAs($actor)
            ->get(route('academic.class-groups.index', [
                'search' => 'VII',
                'filter' => [
                    'academic_year_id' => $fixture['academicYearId'],
                    'academic_term_id' => $fixture['academicTermId'],
                    'unit_id' => $fixture['unitId'],
                    'curriculum_id' => $fixture['curriculumId'],
                    'status' => 'active',
                ],
                'per_page' => 10,
                'sort' => 'code',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Academic/KelasRombel/pages/Index')
                ->where('classGroups.data.0.code', 'VII-A')
                ->where('classGroups.data.0.name', 'Kelas VII A')
                ->where('classGroups.data.0.status', 'active')
                ->where('classGroups.data.0.capacity', 32)
                ->where('classGroups.data.0.academic_year.code', '2026-2027')
                ->where('classGroups.data.0.academic_term.code', '2026-2027-GANJIL')
                ->where('classGroups.data.0.unit.code', 'MTS-UI')
                ->where('classGroups.data.0.curriculum.code', 'KUR-UI')
                ->where('classGroups.data.0.class_level.code', 'VII')
                ->where('classGroups.meta.total', 1)
                ->where('filters.search', 'VII')
                ->where('filters.filter.status', 'active')
                ->where('filters.filter.unit_id', $fixture['unitId'])
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'code')
                ->where('pagination.defaultPerPage', 25)
                ->where('options.academicYears.0.id', $fixture['academicYearId'])
                ->where('options.academicTerms.0.id', $fixture['academicTermId'])
                ->where('options.units.0.id', $fixture['unitId'])
                ->where('options.curricula.0.id', $fixture['curriculumId'])
                ->where('options.classLevels.0.id', $fixture['classLevelId'])
                ->where('canManage', false)
                ->where('canPlacement', false)
                ->where('canArchive', false));
    }

    public function test_menampilkan_halaman_inertia_detail_kelas_rombel(): void
    {
        $view = Permission::create(['name' => 'kelas_rombel.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $fixture = $this->createClassGroupFixture(withDetails: true);

        $this->actingAs($actor)
            ->get(route('academic.class-groups.show', $fixture['classGroupId']))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Academic/KelasRombel/pages/Show')
                ->where('classGroup.code', 'VII-A')
                ->where('classGroup.name', 'Kelas VII A')
                ->where('classGroup.students.0.student_no', 'NIS-UI-ROMBEL')
                ->where('classGroup.students.0.student_name', 'Santri Rombel UI')
                ->where('classGroup.homerooms.0.employee_name', 'Ustaz UI Rombel')
                ->where('options.students.0.code', 'NIS-UI-ROMBEL')
                ->where('options.employees.0.code', 'PEG-UI-ROMBEL'));
    }

    public function test_web_mutation_ui_kelas_rombel_memakai_action_application(): void
    {
        $permissions = collect([
            'kelas_rombel.view',
            'kelas_rombel.manage',
            'kelas_rombel.placement',
            'kelas_rombel.archive',
        ])->map(fn (string $name): Permission => Permission::create(['name' => $name, 'guard_name' => 'web']));
        $actor = $this->createUser();
        $actor->givePermissionTo($permissions);
        $fixture = $this->createClassGroupFixture();

        $this->actingAs($actor)
            ->post(route('academic.class-groups.curricula.store'), [
                'code' => 'KUR-WEB',
                'name' => 'Kurikulum Web',
                'description' => 'Kurikulum dibuat dari UI.',
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $curriculumId = (string) DB::table('academic_curricula')->where('code', 'KUR-WEB')->value('id');

        $this->actingAs($actor)
            ->post(route('academic.class-groups.levels.store'), [
                'unit_id' => $fixture['unitId'],
                'code' => 'VIII',
                'name' => 'Kelas VIII',
                'sequence' => 8,
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $levelId = (string) DB::table('class_levels')->where('code', 'VIII')->value('id');

        $this->actingAs($actor)
            ->post(route('academic.class-groups.store'), [
                'academic_year_id' => $fixture['academicYearId'],
                'academic_term_id' => $fixture['academicTermId'],
                'unit_id' => $fixture['unitId'],
                'curriculum_id' => $curriculumId,
                'class_level_id' => $levelId,
                'code' => 'VIII-A',
                'name' => 'Kelas VIII A',
                'capacity' => 30,
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $classGroupId = (string) DB::table('class_groups')->where('code', 'VIII-A')->value('id');
        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-WEB-ROMBEL',
            'full_name' => 'Santri Web Rombel',
            'primary_unit_id' => $fixture['unitId'],
            'status' => 'active',
        ]);
        $employee = EmployeeRecord::query()->create([
            'employee_no' => 'PEG-WEB-ROMBEL',
            'name' => 'Ustaz Web Rombel',
            'preferred_name' => 'Ustaz Web',
            'employment_type' => 'teacher',
            'primary_unit_id' => $fixture['unitId'],
            'position' => 'Guru Web',
            'status' => 'active',
            'joined_on' => '2026-07-01',
            'left_on' => null,
            'notes' => null,
        ]);

        $this->actingAs($actor)
            ->post(route('academic.class-groups.students.store', $classGroupId), [
                'student_id' => $student->id,
                'joined_on' => '2026-07-15',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->post(route('academic.class-groups.homerooms.store', $classGroupId), [
                'employee_id' => $employee->id,
                'assigned_on' => '2026-07-01',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('academic.class-groups.archive', $classGroupId), [
                'reason' => 'Rombel web selesai dipakai.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('academic.class-groups.restore', $classGroupId))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_group_students', [
            'class_group_id' => $classGroupId,
            'student_id' => $student->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('class_group_homerooms', [
            'class_group_id' => $classGroupId,
            'employee_id' => $employee->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('class_groups', [
            'id' => $classGroupId,
            'status' => 'active',
            'archived_at' => null,
        ]);
    }

    public function test_menghubungkan_ui_kelas_rombel_ke_komponen_canonical_dan_sidebar(): void
    {
        $index = $this->sourceFile('js/pages/Academic/KelasRombel/pages/Index.tsx');
        $dashboard = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelDashboard.tsx');
        $show = $this->sourceFile('js/pages/Academic/KelasRombel/pages/Show.tsx');
        $filters = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelFilters.tsx');
        $table = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelTable.tsx');
        $summary = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelPagination.tsx');
        $detail = $this->sourceFile('js/pages/Academic/KelasRombel/components/KelasRombelDetailPanel.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString('KelasRombelDashboard', $index);
        self::assertStringContainsString("canAccess(auth, 'kelas_rombel.view')", $dashboard);
        self::assertStringContainsString('KelasRombelSummaryCards', $dashboard);
        self::assertStringContainsString('KelasRombelFilters', $dashboard);
        self::assertStringContainsString('KelasRombelTable', $dashboard);
        self::assertStringContainsString('KelasRombelPagination', $dashboard);
        self::assertStringContainsString('KelasRombelMutationDialogs', $dashboard);
        self::assertStringContainsString('Cari rombel', $filters);
        self::assertStringContainsString('Status rombel', $filters);
        self::assertStringContainsString('Status arsip', $filters);
        self::assertStringContainsString('Tahun ajaran', $filters);
        self::assertStringContainsString('Kurikulum', $filters);
        self::assertStringContainsString('Rombel', $table);
        self::assertStringContainsString('Wali kelas', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total rombel', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Daftar santri', $detail);
        self::assertStringContainsString('Riwayat wali kelas', $detail);
        self::assertStringContainsString('Tempatkan santri', $detail);
        self::assertStringContainsString('Tetapkan wali', $detail);
        self::assertStringContainsString('Arsipkan', $detail);
        self::assertStringContainsString('Tambah kurikulum', $this->sourceFile('js/pages/Academic/KelasRombel/components/CurriculumFormDialog.tsx'));
        self::assertStringContainsString('Tambah tingkat kelas', $this->sourceFile('js/pages/Academic/KelasRombel/components/ClassLevelFormDialog.tsx'));
        self::assertStringContainsString('Tambah rombel', $this->sourceFile('js/pages/Academic/KelasRombel/components/ClassGroupFormDialog.tsx'));
        self::assertStringContainsString('Kelas / Rombel / Kurikulum', $show);
        self::assertStringContainsString('Kelas / Rombel / Kurikulum', $navigation);
        self::assertStringContainsString('academic.class-groups.index', $navigation);
        self::assertStringContainsString("'kelas_rombel.view'", $navigation);
    }

    /** @return array<string, string> */
    private function createClassGroupFixture(bool $withDetails = false): array
    {
        $now = now();
        $academicYearId = (string) Str::ulid();
        $academicTermId = (string) Str::ulid();
        $unitId = (string) Str::ulid();
        $curriculumId = (string) Str::ulid();
        $classLevelId = (string) Str::ulid();
        $classGroupId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'parent_id' => null,
            'code' => 'MTS-UI',
            'name' => 'MTs UI',
            'type' => 'education_unit',
            'status' => 'active',
            'location_name' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('academic_years')->insert([
            'id' => $academicYearId,
            'code' => '2026-2027',
            'name' => 'Tahun Ajaran 2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('academic_terms')->insert([
            'id' => $academicTermId,
            'academic_year_id' => $academicYearId,
            'code' => '2026-2027-GANJIL',
            'name' => 'Semester Ganjil',
            'sequence' => 1,
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('academic_curricula')->insert([
            'id' => $curriculumId,
            'code' => 'KUR-UI',
            'name' => 'Kurikulum UI',
            'description' => null,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('class_levels')->insert([
            'id' => $classLevelId,
            'unit_id' => $unitId,
            'code' => 'VII',
            'name' => 'Kelas VII',
            'sequence' => 7,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('class_groups')->insert([
            'id' => $classGroupId,
            'academic_year_id' => $academicYearId,
            'academic_term_id' => $academicTermId,
            'unit_id' => $unitId,
            'curriculum_id' => $curriculumId,
            'class_level_id' => $classLevelId,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'capacity' => 32,
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($withDetails) {
            $student = StudentRecord::factory()->create([
                'student_no' => 'NIS-UI-ROMBEL',
                'full_name' => 'Santri Rombel UI',
                'primary_unit_id' => $unitId,
                'status' => 'active',
            ]);
            $employee = EmployeeRecord::query()->create([
                'employee_no' => 'PEG-UI-ROMBEL',
                'name' => 'Ustaz UI Rombel',
                'preferred_name' => 'Ustaz UI',
                'employment_type' => 'teacher',
                'primary_unit_id' => $unitId,
                'position' => 'Guru UI',
                'status' => 'active',
                'joined_on' => '2026-07-01',
                'left_on' => null,
                'notes' => null,
            ]);

            DB::table('class_group_students')->insert([
                'id' => (string) Str::ulid(),
                'class_group_id' => $classGroupId,
                'academic_term_id' => $academicTermId,
                'student_id' => $student->id,
                'student_no' => $student->student_no,
                'joined_on' => '2026-07-15',
                'left_on' => null,
                'status' => 'active',
                'reason' => null,
                'active_period_student_key' => $academicTermId.':'.$student->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('class_group_homerooms')->insert([
                'id' => (string) Str::ulid(),
                'class_group_id' => $classGroupId,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'assigned_on' => '2026-07-01',
                'ended_on' => null,
                'status' => 'active',
                'reason' => null,
                'active_class_group_key' => $classGroupId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return [
            'academicYearId' => $academicYearId,
            'academicTermId' => $academicTermId,
            'unitId' => $unitId,
            'curriculumId' => $curriculumId,
            'classLevelId' => $classLevelId,
            'classGroupId' => $classGroupId,
        ];
    }

    private function createUser(): User
    {
        $user = User::factory()->create();

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function sourceFile(string $path): string
    {
        $contents = file_get_contents(resource_path($path));

        if ($contents === false) {
            self::fail("File frontend tidak ditemukan: {$path}");
        }

        return $contents;
    }
}
