<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PresensiSantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendaftarkan_route_web_inertia_presensi_santri_untuk_ziggy(): void
    {
        self::assertTrue(Route::has('pesantrian.student-attendances.index'));
        self::assertTrue(Route::has('pesantrian.student-attendances.show'));
    }

    public function test_menolak_actor_tanpa_permission_presensi_santri_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('pesantrian.student-attendances.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_presensi_santri(): void
    {
        $view = Permission::create(['name' => 'presensi_santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        $attendance = StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-06',
            'context_type' => 'class_group',
            'context_id' => null,
            'context_name' => 'VII A',
            'session_code' => 'KBM-PAGI-UI',
            'session_name' => 'KBM Pagi',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $actor->id,
        ]);
        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $attendance->id,
            'student_id' => StudentRecord::factory()->create([
                'student_no' => 'NIS-PS-001',
                'full_name' => 'Aisyah Presensi',
                'status' => 'active',
            ])->id,
            'student_no' => 'NIS-PS-001',
            'student_name' => 'Aisyah Presensi',
            'status' => 'present',
        ]);
        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $attendance->id,
            'student_id' => StudentRecord::factory()->create([
                'student_no' => 'NIS-PS-002',
                'full_name' => 'Hasan Terlambat',
                'status' => 'active',
            ])->id,
            'student_no' => 'NIS-PS-002',
            'student_name' => 'Hasan Terlambat',
            'status' => 'late',
            'minutes_late' => 12,
        ]);

        StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-07',
            'context_type' => 'dormitory',
            'context_name' => 'Asrama Putra',
            'session_code' => 'ASR-MALAM-NO',
            'session_name' => 'Malam',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-attendances.index', [
                'search' => 'KBM',
                'filter' => [
                    'date_from' => '2026-09-01',
                    'date_to' => '2026-09-30',
                    'context_type' => 'class_group',
                    'status' => 'submitted',
                ],
                'per_page' => 10,
                'sort' => 'session_code',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PresensiSantri/pages/Index')
                ->where('attendances.data.0.id', $attendance->id)
                ->where('attendances.data.0.attendance_date', '2026-09-06')
                ->where('attendances.data.0.context_type', 'class_group')
                ->where('attendances.data.0.context_name', 'VII A')
                ->where('attendances.data.0.session_code', 'KBM-PAGI-UI')
                ->where('attendances.data.0.session_name', 'KBM Pagi')
                ->where('attendances.data.0.status', 'submitted')
                ->where('attendances.data.0.summary.total', 2)
                ->where('attendances.data.0.summary.present', 1)
                ->where('attendances.data.0.summary.late', 1)
                ->where('attendances.meta.total', 1)
                ->where('filters.search', 'KBM')
                ->where('filters.filter.date_from', '2026-09-01')
                ->where('filters.filter.date_to', '2026-09-30')
                ->where('filters.filter.context_type', 'class_group')
                ->where('filters.filter.status', 'submitted')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'session_code')
                ->where('pagination.defaultPerPage', 25)
                ->where('canManage', false)
                ->where('canSubmit', false)
                ->where('canRevise', false)
                ->where('canArchive', false));
    }

    public function test_menampilkan_halaman_inertia_detail_presensi_santri(): void
    {
        $view = Permission::create(['name' => 'presensi_santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        $attendance = StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-06',
            'context_type' => 'activity',
            'context_name' => 'Muhadharah',
            'session_code' => 'MUHADHARAH-UI',
            'session_name' => 'Muhadharah Malam Jumat',
            'status' => 'revised',
        ]);
        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $attendance->id,
            'student_id' => StudentRecord::factory()->create([
                'student_no' => 'NIS-PS-003',
                'full_name' => 'Nadia Detail',
                'status' => 'active',
            ])->id,
            'student_no' => 'NIS-PS-003',
            'student_name' => 'Nadia Detail',
            'status' => 'excused',
            'note' => 'Izin keluarga.',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-attendances.show', $attendance->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PresensiSantri/pages/Show')
                ->where('attendance.id', $attendance->id)
                ->where('attendance.session_code', 'MUHADHARAH-UI')
                ->where('attendance.session_name', 'Muhadharah Malam Jumat')
                ->where('attendance.status', 'revised')
                ->where('attendance.entries.0.student_no', 'NIS-PS-003')
                ->where('attendance.entries.0.student_name', 'Nadia Detail')
                ->where('attendance.entries.0.status', 'excused')
                ->where('attendance.entries.0.note', 'Izin keluarga.')
                ->where('canManage', false)
                ->where('canSubmit', false)
                ->where('canRevise', false)
                ->where('canArchive', false));
    }

    public function test_menghubungkan_ui_presensi_santri_ke_komponen_canonical_dan_sidebar(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/pages/Show.tsx');
        $dashboard = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriDashboard.tsx');
        $filters = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriPagination.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/PresensiSantri/components/PresensiSantriDetailPanel.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString('PresensiSantriDashboard', $index);
        self::assertStringContainsString('PresensiSantriDetailPanel', $show);
        self::assertStringContainsString("canAccess(auth, 'presensi_santri.view')", $dashboard);
        self::assertStringContainsString('PresensiSantriSummaryCards', $dashboard);
        self::assertStringContainsString('PresensiSantriFilters', $dashboard);
        self::assertStringContainsString('PresensiSantriTable', $dashboard);
        self::assertStringContainsString('PresensiSantriPagination', $dashboard);
        self::assertStringContainsString('Cari sesi presensi', $filters);
        self::assertStringContainsString('Tanggal mulai', $filters);
        self::assertStringContainsString('Konteks presensi', $filters);
        self::assertStringContainsString('Status sesi', $filters);
        self::assertStringContainsString('Kode sesi', $table);
        self::assertStringContainsString('Ringkasan hadir', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total sesi', $summary);
        self::assertStringContainsString('Sesi final', $summary);
        self::assertStringContainsString('Butuh tindak lanjut', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Ringkasan status', $detail);
        self::assertStringContainsString('Daftar santri', $detail);
        self::assertStringContainsString('NIS', $detail);
        self::assertStringContainsString('Presensi Santri', $navigation);
        self::assertStringContainsString('pesantrian.student-attendances.index', $navigation);
        self::assertStringContainsString("'presensi_santri.view'", $navigation);
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
