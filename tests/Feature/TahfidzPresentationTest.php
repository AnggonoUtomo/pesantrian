<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class TahfidzPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendaftarkan_route_web_inertia_tahfidz_untuk_ziggy(): void
    {
        self::assertTrue(Route::has('pesantrian.tahfidz.index'));
        self::assertTrue(Route::has('pesantrian.tahfidz.show'));
    }

    public function test_menolak_actor_tanpa_permission_tahfidz_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('pesantrian.tahfidz.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_tahfidz(): void
    {
        $this->withoutVite();

        $actor = $this->viewer();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create([
            'code' => 'THF-UI',
            'name' => 'Tahfidz UI',
        ]);
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-THF-UI',
            'student_name' => 'Aisyah Hafalan UI',
            'academic_period_id' => $references['academic_term_id'],
            'period_label' => 'Semester Ganjil 2026/2027',
            'target_juz' => 1,
        ]);
        $submission = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-THF-UI',
            'student_name' => 'Aisyah Hafalan UI',
            'supervisor_id' => $references['employee_id'],
            'supervisor_name' => 'Ustadz UI Tahfidz',
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'surah' => 'Al-Baqarah',
            'ayah_from' => 1,
            'ayah_to' => 5,
            'status' => 'submitted',
        ]);
        TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-THF-UI',
            'student_name' => 'Aisyah Hafalan UI',
            'submission_date' => '2026-09-08',
            'type' => 'murojaah',
            'status' => 'accepted',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.tahfidz.index', [
                'search' => 'Ustadz UI',
                'filter' => [
                    'type' => 'new_memorization',
                    'status' => 'submitted',
                    'date_from' => '2026-09-01',
                    'date_to' => '2026-09-30',
                ],
                'per_page' => 10,
                'sort' => 'submission_date',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Tahfidz/pages/Index')
                ->where('submissions.data.0.id', $submission->id)
                ->where('submissions.data.0.program.code', 'THF-UI')
                ->where('submissions.data.0.student_no', 'NIS-THF-UI')
                ->where('submissions.data.0.student_name', 'Aisyah Hafalan UI')
                ->where('submissions.data.0.supervisor_name', 'Ustadz UI Tahfidz')
                ->where('submissions.data.0.submission_date', '2026-09-07')
                ->where('submissions.data.0.type', 'new_memorization')
                ->where('submissions.data.0.status', 'submitted')
                ->where('submissions.data.0.summary.has_target', true)
                ->where('submissions.meta.total', 1)
                ->where('filters.search', 'Ustadz UI')
                ->where('filters.filter.type', 'new_memorization')
                ->where('filters.filter.status', 'submitted')
                ->where('filters.filter.date_from', '2026-09-01')
                ->where('filters.filter.date_to', '2026-09-30')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'submission_date')
                ->where('pagination.defaultPerPage', 25)
                ->where('canManage', false)
                ->where('canRecord', false)
                ->where('canReview', false)
                ->where('canArchive', false));
    }

    public function test_menampilkan_halaman_inertia_detail_tahfidz(): void
    {
        $this->withoutVite();

        $actor = $this->viewer();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create([
            'code' => 'THF-DETAIL',
            'name' => 'Tahfidz Detail',
        ]);
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-THF-DTL',
            'student_name' => 'Hasan Detail',
            'academic_period_id' => $references['academic_term_id'],
            'period_label' => 'Semester Ganjil 2026/2027',
            'target_surah' => 'Ali Imran',
            'target_ayah_from' => 1,
            'target_ayah_to' => 20,
        ]);
        $submission = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-THF-DTL',
            'student_name' => 'Hasan Detail',
            'supervisor_id' => $references['employee_id'],
            'supervisor_name' => 'Ustadz Detail Tahfidz',
            'submission_date' => '2026-09-07',
            'type' => 'murojaah',
            'surah' => 'Ali Imran',
            'ayah_from' => 1,
            'ayah_to' => 10,
            'status' => 'needs_revision',
            'quality_note' => 'Perlu ulang ayat akhir.',
        ]);
        TahfidzSubmissionRevisionRecord::factory()->create([
            'submission_id' => $submission->id,
            'reason' => 'Koreksi makhraj.',
            'summary' => ['status' => 'needs_revision'],
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.tahfidz.show', $submission->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Tahfidz/pages/Show')
                ->where('submission.id', $submission->id)
                ->where('submission.program.name', 'Tahfidz Detail')
                ->where('submission.target.period_label', 'Semester Ganjil 2026/2027')
                ->where('submission.student_no', 'NIS-THF-DTL')
                ->where('submission.student_name', 'Hasan Detail')
                ->where('submission.supervisor_name', 'Ustadz Detail Tahfidz')
                ->where('submission.type', 'murojaah')
                ->where('submission.status', 'needs_revision')
                ->where('submission.summary.has_revision', true)
                ->where('submission.summary.revision_count', 1)
                ->where('submission.revisions.0.reason', 'Koreksi makhraj.')
                ->where('canManage', false)
                ->where('canRecord', false)
                ->where('canReview', false)
                ->where('canArchive', false));
    }

    public function test_menghubungkan_ui_tahfidz_ke_komponen_canonical_dan_sidebar(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/Tahfidz/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/Tahfidz/pages/Show.tsx');
        $dashboard = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzDashboard.tsx');
        $filters = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzPagination.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/Tahfidz/components/TahfidzDetailPanel.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString('TahfidzDashboard', $index);
        self::assertStringContainsString('TahfidzDetailPanel', $show);
        self::assertStringContainsString("canAccess(auth, 'tahfidz.view')", $dashboard);
        self::assertStringContainsString('TahfidzSummaryCards', $dashboard);
        self::assertStringContainsString('TahfidzFilters', $dashboard);
        self::assertStringContainsString('TahfidzTable', $dashboard);
        self::assertStringContainsString('TahfidzPagination', $dashboard);
        self::assertStringContainsString('Cari setoran', $filters);
        self::assertStringContainsString('Tanggal mulai', $filters);
        self::assertStringContainsString('Tipe setoran', $filters);
        self::assertStringContainsString('Status setoran', $filters);
        self::assertStringContainsString('Program', $table);
        self::assertStringContainsString('Pembimbing', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total setoran', $summary);
        self::assertStringContainsString('Setoran diterima', $summary);
        self::assertStringContainsString('Butuh tindak lanjut', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Detail setoran', $detail);
        self::assertStringContainsString('Target hafalan', $detail);
        self::assertStringContainsString('Riwayat koreksi', $detail);
        self::assertStringContainsString('Tahfidz / Hafalan', $navigation);
        self::assertStringContainsString('pesantrian.tahfidz.index', $navigation);
        self::assertStringContainsString("'tahfidz.view'", $navigation);
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'tahfidz.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        return $actor;
    }

    private function createUser(): User
    {
        $user = User::factory()->create();

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    /** @return array{student_id: string, academic_term_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicTermId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-THF-UI',
            'name' => 'MA Tahfidz UI',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-THF-UI',
            'full_name' => 'Aisyah Hafalan UI',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_years')->insert([
            'id' => $academicYearId,
            'code' => '2026-2027-THF',
            'name' => 'Tahun Ajaran 2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_terms')->insert([
            'id' => $academicTermId,
            'academic_year_id' => $academicYearId,
            'code' => '2026-1-THF',
            'name' => 'Semester Ganjil 2026/2027',
            'sequence' => 1,
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-THF-UI',
            'name' => 'Ustadz UI Tahfidz',
            'employment_type' => 'teacher',
            'position' => 'Pembimbing Tahfidz',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'student_id' => $studentId,
            'academic_term_id' => $academicTermId,
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
