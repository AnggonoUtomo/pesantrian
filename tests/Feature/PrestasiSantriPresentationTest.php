<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRevisionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PrestasiSantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendaftarkan_route_web_inertia_prestasi_santri_untuk_ziggy(): void
    {
        self::assertTrue(Route::has('pesantrian.prestasi-santri.index'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.categories.store'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.categories.update'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.categories.archive'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.store'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.update'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.submit'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.verify'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.revise'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.void'));
        self::assertTrue(Route::has('pesantrian.prestasi-santri.show'));
    }

    public function test_menolak_actor_tanpa_permission_prestasi_santri_view(): void
    {
        $actor = User::factory()->create();

        self::assertInstanceOf(User::class, $actor);

        $this->actingAs($actor)
            ->get(route('pesantrian.prestasi-santri.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_prestasi_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor(['prestasi_santri.view', 'prestasi_santri.verify']);
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'UI-AKD',
            'name' => 'Akademik UI',
        ]);
        $achievement = StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-UI-001',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-PRS-UI',
            'student_name' => 'Ahmad Prestasi UI',
            'academic_period_id' => $references['academic_period_id'],
            'academic_period_label' => 'Semester Ganjil - 2026/2027',
            'mentor_employee_id' => $references['employee_id'],
            'mentor_name' => 'Ustadz Prestasi UI',
            'title' => 'Juara Olimpiade Matematika UI',
            'achievement_type' => 'competition',
            'level' => 'national',
            'result' => 'Juara 1',
            'organizer' => 'Kemenag UI',
            'event_name' => 'Olimpiade UI',
            'event_location' => 'Jakarta UI',
            'achieved_on' => '2026-09-24',
            'description' => 'Prestasi akademik UI.',
            'status' => 'submitted',
            'submitted_at' => '2026-09-24 08:00:00',
            'submitted_by' => $actor->id,
        ]);
        StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-UI-002',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-PRS-UI',
            'student_name' => 'Tidak Cocok',
            'title' => 'Prestasi tidak cocok',
            'level' => 'internal',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.prestasi-santri.index', [
                'search' => 'Olimpiade UI',
                'filter' => [
                    'date_from' => '2026-09-01',
                    'date_to' => '2026-09-30',
                    'status' => 'submitted',
                    'level' => 'national',
                    'category_id' => $category->id,
                    'student_id' => $references['student_id'],
                    'mentor_employee_id' => $references['employee_id'],
                    'academic_period_id' => $references['academic_period_id'],
                ],
                'per_page' => 10,
                'sort' => 'achievement_no',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PrestasiSantri/pages/Index')
                ->where('achievements.data.0.id', $achievement->id)
                ->where('achievements.data.0.achievement_no', 'PRS-UI-001')
                ->where('achievements.data.0.student_no', 'NIS-PRS-UI')
                ->where('achievements.data.0.student_name', 'Ahmad Prestasi UI')
                ->where('achievements.data.0.academic_period_label', 'Semester Ganjil - 2026/2027')
                ->where('achievements.data.0.category.code', 'UI-AKD')
                ->where('achievements.data.0.level', 'national')
                ->where('achievements.data.0.status', 'submitted')
                ->where('achievements.data.0.summary.needs_action', true)
                ->where('achievements.meta.total', 1)
                ->where('filters.search', 'Olimpiade UI')
                ->where('filters.filter.status', 'submitted')
                ->where('filters.filter.level', 'national')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'achievement_no')
                ->where('options.statuses.1.label', 'Menunggu verifikasi')
                ->where('options.levels.4.label', 'Nasional')
                ->where('options.types.0.label', 'Lomba/Kompetisi')
                ->where('options.categories.0.label', 'Akademik UI')
                ->where('options.students.0.label', 'Ahmad Prestasi UI (NIS-PRS-UI)')
                ->where('options.officers.0.label', 'Ustadz Prestasi UI (PEG-PRS-UI)')
                ->where('options.academicPeriods.0.label', 'Semester Ganjil - 2026/2027')
                ->where('canManage', false)
                ->where('canRecord', false)
                ->where('canVerify', true)
                ->where('canArchive', false)
                ->where('pagination.defaultPerPage', 25));
    }

    public function test_mengelola_kategori_draft_dan_submit_prestasi_dari_route_web(): void
    {
        $actor = $this->actor([
            'prestasi_santri.view',
            'prestasi_santri.manage',
            'prestasi_santri.record',
        ]);
        $references = $this->seedReferences();

        $this->actingAs($actor)
            ->post(route('pesantrian.prestasi-santri.categories.store'), [
                'code' => 'UI-WEB',
                'name' => 'Web Mutation UI',
                'description' => 'Kategori dibuat dari route web.',
            ])
            ->assertRedirect();

        $category = StudentAchievementCategoryRecord::query()
            ->where('code', 'UI-WEB')
            ->firstOrFail();

        $this->actingAs($actor)
            ->patch(route('pesantrian.prestasi-santri.categories.update', $category->id), [
                'name' => 'Web Mutation UI Updated',
                'description' => 'Kategori diperbarui dari route web.',
            ])
            ->assertRedirect();

        self::assertSame(
            'Web Mutation UI Updated',
            $category->fresh()?->name,
        );

        $this->actingAs($actor)
            ->post(route('pesantrian.prestasi-santri.store'), [
                'student_id' => $references['student_id'],
                'category_id' => $category->id,
                'academic_period_id' => $references['academic_period_id'],
                'mentor_employee_id' => $references['employee_id'],
                'title' => 'Prestasi dari route web',
                'achievement_type' => 'competition',
                'level' => 'province',
                'result' => 'Juara 1',
                'organizer' => 'Panitia UI',
                'event_name' => 'Lomba UI',
                'event_location' => 'Bandung UI',
                'achieved_on' => '2026-09-24',
                'description' => 'Prestasi dibuat dari route web.',
            ])
            ->assertRedirect();

        $achievement = StudentAchievementRecord::query()
            ->where('title', 'Prestasi dari route web')
            ->firstOrFail();

        self::assertSame('draft', $achievement->status);

        $this->actingAs($actor)
            ->patch(route('pesantrian.prestasi-santri.submit', $achievement->id))
            ->assertRedirect(route('pesantrian.prestasi-santri.show', $achievement->id));

        self::assertSame(
            'submitted',
            $achievement->fresh()?->status,
        );
    }

    public function test_menampilkan_halaman_inertia_detail_prestasi_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor(['prestasi_santri.view', 'prestasi_santri.archive']);
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'UI-TAH',
            'name' => 'Tahfidz UI',
        ]);
        $achievement = StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-UI-DETAIL',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-PRS-UI',
            'student_name' => 'Ahmad Prestasi UI',
            'academic_period_id' => $references['academic_period_id'],
            'academic_period_label' => 'Semester Ganjil - 2026/2027',
            'mentor_employee_id' => $references['employee_id'],
            'mentor_name' => 'Ustadz Prestasi UI',
            'title' => 'Juara Musabaqah Hifzhil Quran UI',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 2',
            'organizer' => 'Kanwil UI',
            'event_name' => 'MHQ UI',
            'event_location' => 'Bandung UI',
            'achieved_on' => '2026-09-24',
            'description' => 'Detail prestasi santri UI.',
            'status' => 'verified',
            'submitted_at' => '2026-09-24 08:00:00',
            'submitted_by' => $actor->id,
            'verified_at' => '2026-09-25 09:00:00',
            'verified_by' => $actor->id,
            'verification_note' => 'Piagam sudah dicek.',
        ]);
        StudentAchievementRevisionRecord::query()->create([
            'achievement_id' => $achievement->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'reason' => 'Piagam sudah dicek.',
            'changed_by' => $actor->id,
            'changed_at' => now(),
            'summary' => ['action' => 'verify', 'to_status' => 'verified'],
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.prestasi-santri.show', $achievement->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PrestasiSantri/pages/Show')
                ->where('achievement.achievement_no', 'PRS-UI-DETAIL')
                ->where('achievement.student_name', 'Ahmad Prestasi UI')
                ->where('achievement.category.code', 'UI-TAH')
                ->where('achievement.level', 'province')
                ->where('achievement.status', 'verified')
                ->where('achievement.summary.is_final', true)
                ->where('achievement.revisions.0.reason', 'Piagam sudah dicek.')
                ->where('canArchive', true)
                ->where('canVerify', false));
    }

    public function test_menghubungkan_ui_prestasi_ke_komponen_canonical_sidebar_dan_ziggy(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/pages/Show.tsx');
        $dashboard = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriDashboard.tsx');
        $filters = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriPagination.tsx');
        $empty = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriEmptyState.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriDetailPanel.tsx');
        $actionBar = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriActionBar.tsx');
        $categoryPanel = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriCategoryPanel.tsx');
        $categoryDialog = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriCategoryDialog.tsx');
        $mutation = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriMutationDialog.tsx');
        $lifecycle = $this->sourceFile('js/pages/Pesantrian/PrestasiSantri/components/PrestasiSantriLifecycleDialogs.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');
        $ziggy = file_get_contents(config_path('ziggy.php'));

        self::assertStringContainsString('PrestasiSantriDashboard', $index);
        self::assertStringContainsString('PrestasiSantriDetailPanel', $show);
        self::assertStringContainsString("canAccess(auth, 'prestasi_santri.view')", $dashboard);
        self::assertStringContainsString('PrestasiSantriSummaryCards', $dashboard);
        self::assertStringContainsString('PrestasiSantriFilters', $dashboard);
        self::assertStringContainsString('PrestasiSantriTable', $dashboard);
        self::assertStringContainsString('PrestasiSantriPagination', $dashboard);
        self::assertStringContainsString('PrestasiSantriActionBar', $dashboard);
        self::assertStringContainsString('PrestasiSantriCategoryPanel', $dashboard);
        self::assertStringContainsString('PrestasiSantriMutationDialog', $dashboard);
        self::assertStringContainsString('Cari prestasi', $filters);
        self::assertStringContainsString('Status prestasi', $filters);
        self::assertStringContainsString('Tingkat', $filters);
        self::assertStringContainsString('Kategori', $filters);
        self::assertStringContainsString('Nomor prestasi', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Submit', $table);
        self::assertStringContainsString('Verifikasi', $table);
        self::assertStringContainsString('Revisi', $table);
        self::assertStringContainsString('Batalkan', $table);
        self::assertStringContainsString('Total prestasi', $summary);
        self::assertStringContainsString('Butuh tindak lanjut', $summary);
        self::assertStringContainsString('Riwayat final', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Belum ada prestasi yang cocok', $empty);
        self::assertStringContainsString('Detail Prestasi', $detail);
        self::assertStringContainsString('Aksi Prestasi', $detail);
        self::assertStringContainsString('Lifecycle prestasi', $detail);
        self::assertStringContainsString('Histori revisi', $detail);
        self::assertStringContainsString('Lifecycle Prestasi Santri', $actionBar);
        self::assertStringContainsString('Tambah prestasi', $actionBar);
        self::assertStringContainsString('Buat kategori', $actionBar);
        self::assertStringContainsString('Kategori prestasi', $categoryPanel);
        self::assertStringContainsString('Edit kategori', $categoryPanel);
        self::assertStringContainsString('Arsipkan', $categoryPanel);
        self::assertStringContainsString('Buat kategori prestasi', $categoryDialog);
        self::assertStringContainsString('Simpan kategori', $categoryDialog);
        self::assertStringContainsString('Buat draft prestasi', $mutation);
        self::assertStringContainsString('Simpan perubahan', $mutation);
        self::assertStringContainsString('Submit prestasi', $lifecycle);
        self::assertStringContainsString('Verifikasi prestasi', $lifecycle);
        self::assertStringContainsString('Minta revisi prestasi', $lifecycle);
        self::assertStringContainsString('Batalkan prestasi', $lifecycle);
        self::assertStringContainsString('Arsipkan kategori prestasi', $lifecycle);
        self::assertStringContainsString('Prestasi Santri', $navigation);
        self::assertStringContainsString('pesantrian.prestasi-santri.index', $navigation);
        self::assertStringContainsString("'prestasi_santri.view'", $navigation);
        self::assertIsString($ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.index'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.categories.store'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.categories.update'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.categories.archive'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.store'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.update'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.submit'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.verify'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.revise'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.void'", $ziggy);
        self::assertStringContainsString("'pesantrian.prestasi-santri.show'", $ziggy);
    }

    /** @param list<string> $permissions */
    private function actor(array $permissions): User
    {
        $actor = User::factory()->create();

        self::assertInstanceOf(User::class, $actor);

        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }

        return $actor;
    }

    /** @return array{student_id: string, unit_id: string, employee_id: string, academic_period_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicPeriodId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MTP-UI',
            'name' => 'MTs Prestasi UI',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-PRS-UI',
            'full_name' => 'Ahmad Prestasi UI',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-PRS-UI',
            'name' => 'Ustadz Prestasi UI',
            'employment_type' => 'staff',
            'position' => 'Pembina Prestasi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_years')->insert([
            'id' => $academicYearId,
            'code' => '2026-2027',
            'name' => '2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_terms')->insert([
            'id' => $academicPeriodId,
            'academic_year_id' => $academicYearId,
            'code' => 'GANJIL-2026',
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
            'student_id' => $studentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
            'academic_period_id' => $academicPeriodId,
        ];
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
