<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PerizinanSantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendaftarkan_route_web_inertia_perizinan_santri_untuk_ziggy(): void
    {
        self::assertTrue(Route::has('pesantrian.student-permits.index'));
        self::assertTrue(Route::has('pesantrian.student-permits.show'));
    }

    public function test_menolak_actor_tanpa_permission_perizinan_santri_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->get(route('pesantrian.student-permits.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_perizinan_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor([
            'perizinan_santri.view',
            'perizinan_santri.checkout',
            'perizinan_santri.return',
        ]);
        $student = $this->student();
        $permit = StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-UI-001',
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => $student['full_name'],
            'permit_type' => 'home_visit',
            'starts_at' => '2026-09-20 08:00:00',
            'ends_at' => '2026-09-20 17:00:00',
            'destination' => 'Rumah wali UI',
            'reason' => 'Pulang untuk keperluan keluarga.',
            'status' => 'returned',
            'checked_out_at' => '2026-09-20 08:15:00',
            'checked_out_by' => $actor->id,
            'returned_at' => '2026-09-20 18:30:00',
            'returned_by' => $actor->id,
            'return_note' => 'Terlambat karena macet.',
        ]);
        StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-UI-002',
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => 'Tidak Cocok',
            'permit_type' => 'sick',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-permits.index', [
                'search' => 'Rumah wali UI',
                'filter' => [
                    'permit_type' => 'home_visit',
                    'status' => 'returned',
                    'date_from' => '2026-09-01',
                    'date_to' => '2026-09-30',
                    'is_late' => true,
                ],
                'per_page' => 10,
                'sort' => 'permit_no',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PerizinanSantri/pages/Index')
                ->where('permits.data.0.id', $permit->id)
                ->where('permits.data.0.permit_no', 'IZN-UI-001')
                ->where('permits.data.0.student_no', 'NIS-IZN-UI')
                ->where('permits.data.0.student_name', 'Aisyah Izin UI')
                ->where('permits.data.0.permit_type', 'home_visit')
                ->where('permits.data.0.destination', 'Rumah wali UI')
                ->where('permits.data.0.status', 'returned')
                ->where('permits.data.0.summary.is_late', true)
                ->where('permits.meta.total', 1)
                ->where('filters.search', 'Rumah wali UI')
                ->where('filters.filter.permit_type', 'home_visit')
                ->where('filters.filter.status', 'returned')
                ->where('filters.filter.is_late', '1')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'permit_no')
                ->where('options.permitTypes.1.label', 'Pulang ke rumah')
                ->where('options.statuses.4.label', 'Sedang izin')
                ->where('canManage', false)
                ->where('canCheckout', true)
                ->where('canReturn', true)
                ->where('pagination.defaultPerPage', 25));
    }

    public function test_menampilkan_halaman_inertia_detail_perizinan_santri(): void
    {
        $this->withoutVite();

        $actor = $this->actor(['perizinan_santri.view', 'perizinan_santri.archive']);
        $student = $this->student();
        $permit = StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-UI-DETAIL',
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => $student['full_name'],
            'permit_type' => 'activity',
            'starts_at' => '2026-09-21 08:00:00',
            'ends_at' => '2026-09-21 17:00:00',
            'destination' => 'Kecamatan UI',
            'reason' => 'Lomba pidato santri.',
            'guardian_name' => 'Wali Detail UI',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'status' => 'returned',
            'returned_at' => '2026-09-21 18:30:00',
            'returned_by' => $actor->id,
            'return_note' => 'Terlambat karena acara molor.',
        ]);
        StudentPermitRevisionRecord::query()->create([
            'permit_id' => $permit->id,
            'reason' => 'Kembali terlambat karena acara molor.',
            'changed_by' => $actor->id,
            'changed_at' => now(),
            'summary' => ['action' => 'return', 'is_late' => true],
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.student-permits.show', $permit->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PerizinanSantri/pages/Show')
                ->where('permit.permit_no', 'IZN-UI-DETAIL')
                ->where('permit.student_name', 'Aisyah Izin UI')
                ->where('permit.permit_type', 'activity')
                ->where('permit.destination', 'Kecamatan UI')
                ->where('permit.status', 'returned')
                ->where('permit.summary.is_late', true)
                ->where('permit.revisions.0.reason', 'Kembali terlambat karena acara molor.')
                ->where('canArchive', true));
    }

    public function test_menghubungkan_ui_perizinan_ke_komponen_canonical_dan_sidebar(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/pages/Show.tsx');
        $filter = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriPagination.tsx');
        $empty = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriEmptyState.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/PerizinanSantri/components/PerizinanSantriDetailPanel.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString("canAccess(auth, 'perizinan_santri.view')", $index);
        self::assertStringContainsString('PerizinanSantriSummaryCards', $index);
        self::assertStringContainsString('PerizinanSantriFilters', $index);
        self::assertStringContainsString('PerizinanSantriTable', $index);
        self::assertStringContainsString('PerizinanSantriPagination', $index);
        self::assertStringContainsString('Cari izin santri', $filter);
        self::assertStringContainsString('Jenis izin', $filter);
        self::assertStringContainsString('Status izin', $filter);
        self::assertStringContainsString('Keterlambatan', $filter);
        self::assertStringContainsString('Nomor izin', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total izin', $summary);
        self::assertStringContainsString('Izin aktif', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Belum ada izin santri yang cocok', $empty);
        self::assertStringContainsString('Detail Perizinan Santri', $detail);
        self::assertStringContainsString('Histori revisi', $detail);
        self::assertStringContainsString('PerizinanSantriDetailPanel', $show);
        self::assertStringContainsString('Perizinan Santri', $navigation);
        self::assertStringContainsString('pesantrian.student-permits.index', $navigation);
        self::assertStringContainsString("'perizinan_santri.view'", $navigation);
    }

    /** @param list<string> $permissions */
    private function actor(array $permissions): User
    {
        $actor = User::factory()->create();

        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }

        return $actor;
    }

    /** @return array{id: string, student_no: string, full_name: string} */
    private function student(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-IZN',
            'name' => 'MA Perizinan UI',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-IZN-UI',
            'full_name' => 'Aisyah Izin UI',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $studentId,
            'student_no' => 'NIS-IZN-UI',
            'full_name' => 'Aisyah Izin UI',
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
