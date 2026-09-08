<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class KedisiplinanSantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendaftarkan_route_web_inertia_kedisiplinan_santri_untuk_ziggy(): void
    {
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.index'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.store'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.update'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.submit'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.review'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.assign-action'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.resolve'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.void'));
        self::assertTrue(Route::has('pesantrian.student-discipline-cases.show'));
    }

    public function test_menolak_actor_tanpa_permission_kedisiplinan_santri_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->get(route('pesantrian.student-discipline-cases.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_kedisiplinan_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor(['kedisiplinan_santri.view', 'kedisiplinan_santri.review']);
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'UI-ADAB',
            'name' => 'Adab UI',
            'default_severity' => 'moderate',
            'default_points' => 15,
        ]);
        $case = StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-UI-001',
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-DIS-UI',
            'student_name' => 'Ahmad Disiplin UI',
            'unit_id' => $references['unit_id'],
            'unit_name' => 'MTs Kedisiplinan UI',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => 'moderate',
            'points' => 15,
            'occurred_at' => '2026-09-24 07:30:00',
            'location' => 'Masjid UI',
            'description' => 'Terlambat kegiatan pagi UI.',
            'assigned_employee_id' => $references['employee_id'],
            'assigned_employee_name' => 'Ustadz Pembina UI',
            'status' => 'action_assigned',
            'submitted_at' => '2026-09-24 08:00:00',
            'reviewed_at' => '2026-09-24 09:00:00',
            'reviewed_by' => $actor->id,
            'review_note' => 'Sudah diklarifikasi.',
            'action_plan' => 'Refleksi tertulis.',
            'action_assigned_at' => '2026-09-24 10:00:00',
        ]);
        StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-UI-002',
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-DIS-UI',
            'student_name' => 'Tidak Cocok',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'draft',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-discipline-cases.index', [
                'search' => 'Masjid UI',
                'filter' => [
                    'date_from' => '2026-09-01',
                    'date_to' => '2026-09-30',
                    'status' => 'action_assigned',
                    'severity' => 'moderate',
                    'category_id' => $category->id,
                    'student_id' => $references['student_id'],
                    'assigned_employee_id' => $references['employee_id'],
                ],
                'per_page' => 10,
                'sort' => 'case_no',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/KedisiplinanSantri/pages/Index')
                ->where('cases.data.0.id', $case->id)
                ->where('cases.data.0.case_no', 'DIS-UI-001')
                ->where('cases.data.0.student_no', 'NIS-DIS-UI')
                ->where('cases.data.0.student_name', 'Ahmad Disiplin UI')
                ->where('cases.data.0.unit_name', 'MTs Kedisiplinan UI')
                ->where('cases.data.0.category.code', 'UI-ADAB')
                ->where('cases.data.0.severity', 'moderate')
                ->where('cases.data.0.status', 'action_assigned')
                ->where('cases.data.0.summary.needs_action', true)
                ->where('cases.meta.total', 1)
                ->where('filters.search', 'Masjid UI')
                ->where('filters.filter.status', 'action_assigned')
                ->where('filters.filter.severity', 'moderate')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'case_no')
                ->where('options.statuses.3.label', 'Tindakan ditetapkan')
                ->where('options.severities.1.label', 'Sedang')
                ->where('options.categories.0.label', 'Adab UI')
                ->where('options.students.0.label', 'Ahmad Disiplin UI (NIS-DIS-UI)')
                ->where('options.officers.0.label', 'Ustadz Pembina UI (PEG-DIS-UI)')
                ->where('canManage', false)
                ->where('canReview', true)
                ->where('canResolve', false)
                ->where('canArchive', false)
                ->where('pagination.defaultPerPage', 25));
    }

    public function test_menampilkan_halaman_inertia_detail_kedisiplinan_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor(['kedisiplinan_santri.view', 'kedisiplinan_santri.resolve']);
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'UI-TERTIB',
            'name' => 'Ketertiban UI',
        ]);
        $case = StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-UI-DETAIL',
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-DIS-UI',
            'student_name' => 'Ahmad Disiplin UI',
            'unit_id' => $references['unit_id'],
            'unit_name' => 'MTs Kedisiplinan UI',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => 'major',
            'points' => 40,
            'occurred_at' => '2026-09-24 07:30:00',
            'location' => 'Asrama UI',
            'description' => 'Detail kasus kedisiplinan UI.',
            'assigned_employee_id' => $references['employee_id'],
            'assigned_employee_name' => 'Ustadz Pembina UI',
            'status' => 'resolved',
            'submitted_at' => '2026-09-24 08:00:00',
            'reviewed_at' => '2026-09-24 09:00:00',
            'reviewed_by' => $actor->id,
            'review_note' => 'Kronologi sudah jelas.',
            'action_plan' => 'Pembinaan adab selama tiga hari.',
            'action_assigned_at' => '2026-09-24 10:00:00',
            'resolved_at' => '2026-09-25 16:00:00',
            'resolved_by' => $actor->id,
            'resolution_note' => 'Santri sudah menyelesaikan pembinaan.',
        ]);
        StudentDisciplineRevisionRecord::query()->create([
            'case_id' => $case->id,
            'reason' => 'Santri sudah menyelesaikan pembinaan.',
            'changed_by' => $actor->id,
            'changed_at' => now(),
            'summary' => ['action' => 'resolve', 'to_status' => 'resolved'],
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-discipline-cases.show', $case->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/KedisiplinanSantri/pages/Show')
                ->where('case.case_no', 'DIS-UI-DETAIL')
                ->where('case.student_name', 'Ahmad Disiplin UI')
                ->where('case.category.code', 'UI-TERTIB')
                ->where('case.severity', 'major')
                ->where('case.status', 'resolved')
                ->where('case.summary.is_final', true)
                ->where('case.revisions.0.reason', 'Santri sudah menyelesaikan pembinaan.')
                ->where('canResolve', true)
                ->where('canReview', false));
    }

    public function test_mengelola_create_draft_dan_submit_kasus_kedisiplinan_dari_route_web(): void
    {
        $actor = $this->actor(['kedisiplinan_santri.view', 'kedisiplinan_santri.manage']);
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'UI-WEB',
            'name' => 'Web Mutation UI',
            'default_severity' => 'minor',
            'default_points' => 5,
        ]);

        $this->actingAs($actor)
            ->post(route('pesantrian.student-discipline-cases.store'), [
                'student_id' => $references['student_id'],
                'category_id' => $category->id,
                'severity' => 'minor',
                'points' => 5,
                'occurred_at' => '2026-09-24 07:30:00',
                'location' => 'Halaman web',
                'description' => 'Kasus dibuat dari route web.',
                'assigned_employee_id' => $references['employee_id'],
            ])
            ->assertRedirect();

        $case = StudentDisciplineCaseRecord::query()
            ->where('description', 'Kasus dibuat dari route web.')
            ->firstOrFail();

        self::assertSame('draft', $case->status);

        $this->actingAs($actor)
            ->patch(route('pesantrian.student-discipline-cases.submit', $case->id))
            ->assertRedirect(route('pesantrian.student-discipline-cases.show', $case->id));

        self::assertSame(
            'submitted',
            $case->fresh()?->status,
        );
    }

    public function test_menghubungkan_ui_kedisiplinan_ke_komponen_canonical_sidebar_dan_ziggy(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/pages/Show.tsx');
        $dashboard = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriDashboard.tsx');
        $filters = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriPagination.tsx');
        $empty = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriEmptyState.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriDetailPanel.tsx');
        $actionBar = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriActionBar.tsx');
        $mutation = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriMutationDialog.tsx');
        $lifecycle = $this->sourceFile('js/pages/Pesantrian/KedisiplinanSantri/components/KedisiplinanSantriLifecycleDialogs.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');
        $ziggy = file_get_contents(config_path('ziggy.php'));

        self::assertStringContainsString('KedisiplinanSantriDashboard', $index);
        self::assertStringContainsString('KedisiplinanSantriDetailPanel', $show);
        self::assertStringContainsString("canAccess(auth, 'kedisiplinan_santri.view')", $dashboard);
        self::assertStringContainsString('KedisiplinanSantriSummaryCards', $dashboard);
        self::assertStringContainsString('KedisiplinanSantriFilters', $dashboard);
        self::assertStringContainsString('KedisiplinanSantriTable', $dashboard);
        self::assertStringContainsString('KedisiplinanSantriPagination', $dashboard);
        self::assertStringContainsString('KedisiplinanSantriActionBar', $dashboard);
        self::assertStringContainsString('KedisiplinanSantriMutationDialog', $dashboard);
        self::assertStringContainsString('Cari kasus kedisiplinan', $filters);
        self::assertStringContainsString('Status kasus', $filters);
        self::assertStringContainsString('Tingkat', $filters);
        self::assertStringContainsString('Kategori', $filters);
        self::assertStringContainsString('Nomor kasus', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total kasus', $summary);
        self::assertStringContainsString('Butuh tindak lanjut', $summary);
        self::assertStringContainsString('Kasus final', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Belum ada kasus kedisiplinan yang cocok', $empty);
        self::assertStringContainsString('Detail Kedisiplinan Santri', $detail);
        self::assertStringContainsString('Lifecycle kasus', $detail);
        self::assertStringContainsString('Histori revisi', $detail);
        self::assertStringContainsString('Aksi Kedisiplinan', $actionBar);
        self::assertStringContainsString('Buat kasus', $actionBar);
        self::assertStringContainsString('Buat draft kasus', $mutation);
        self::assertStringContainsString('Simpan perubahan', $mutation);
        self::assertStringContainsString('Submit kasus', $lifecycle);
        self::assertStringContainsString('Review kasus', $lifecycle);
        self::assertStringContainsString('Tetapkan tindakan pembinaan', $lifecycle);
        self::assertStringContainsString('Selesaikan kasus', $lifecycle);
        self::assertStringContainsString('Batalkan kasus', $lifecycle);
        self::assertStringContainsString('Pelanggaran / Kedisiplinan', $navigation);
        self::assertStringContainsString('pesantrian.student-discipline-cases.index', $navigation);
        self::assertStringContainsString("'kedisiplinan_santri.view'", $navigation);
        self::assertIsString($ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.index'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.store'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.update'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.submit'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.review'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.assign-action'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.resolve'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.void'", $ziggy);
        self::assertStringContainsString("'pesantrian.student-discipline-cases.show'", $ziggy);
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

    /** @return array{student_id: string, unit_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MTD-UI',
            'name' => 'MTs Kedisiplinan UI',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-DIS-UI',
            'full_name' => 'Ahmad Disiplin UI',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-DIS-UI',
            'name' => 'Ustadz Pembina UI',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'student_id' => $studentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
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
