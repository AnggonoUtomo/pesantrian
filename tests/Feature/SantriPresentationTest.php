<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class SantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_menolak_actor_tanpa_permission_santri_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('pesantrian.students.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_santri(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $unitId = $this->createOrganizationUnit('MTS-S', 'Madrasah Tsanawiyah');

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-UI-001',
            'registration_no' => 'SNTR-UI-001',
            'full_name' => 'Aisyah Santri UI',
            'preferred_name' => 'Aisyah',
            'gender' => 'female',
            'primary_unit_id' => $unitId,
            'entry_date' => '2026-07-15',
            'status' => 'active',
        ]);

        StudentGuardianRecord::query()->create([
            'student_id' => $student->id,
            'guardian_name' => 'Siti UI',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'is_primary' => true,
            'is_emergency_contact' => true,
        ]);

        StudentRecord::factory()->create([
            'student_no' => 'NIS-UI-002',
            'full_name' => 'Budi Tidak Cocok',
            'primary_unit_id' => $unitId,
            'status' => 'inactive',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.students.index', [
                'search' => 'Aisyah',
                'filter' => [
                    'status' => 'active',
                    'primary_unit_id' => $unitId,
                ],
                'per_page' => 10,
                'sort' => 'student_no',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Santri/pages/Index')
                ->where('students.data.0.student_no', 'NIS-UI-001')
                ->where('students.data.0.registration_no', 'SNTR-UI-001')
                ->where('students.data.0.full_name', 'Aisyah Santri UI')
                ->where('students.data.0.preferred_name', 'Aisyah')
                ->where('students.data.0.gender', 'female')
                ->where('students.data.0.primary_unit_id', $unitId)
                ->where('students.data.0.status', 'active')
                ->where('students.data.0.primary_guardian.guardian_name', 'Siti UI')
                ->where('students.meta.total', 1)
                ->where('filters.search', 'Aisyah')
                ->where('filters.filter.status', 'active')
                ->where('filters.filter.primary_unit_id', $unitId)
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'student_no')
                ->where('primaryUnitOptions.0.id', $unitId)
                ->where('primaryUnitOptions.0.name', 'Madrasah Tsanawiyah')
                ->where('primaryUnitOptions.0.code', 'MTS-S')
                ->where('pagination.defaultPerPage', 25));
    }

    public function test_membuat_memperbarui_dan_mengonversi_santri_melalui_web_inertia(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo([$view, $manage]);
        $unitId = $this->createOrganizationUnit('MA-UI', 'Madrasah Aliyah');

        $create = $this->actingAs($actor)->post(route('pesantrian.students.store'), [
            'full_name' => 'Nadia UI Manual',
            'preferred_name' => 'Nadia',
            'gender' => 'female',
            'birth_place' => 'Bandung',
            'birth_date' => '2013-05-10',
            'previous_school' => 'SD Negeri 1',
            'primary_unit_id' => $unitId,
            'entry_date' => '2026-07-15',
            'guardian_name' => 'Siti UI Manual',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'is_emergency_contact' => '1',
        ]);

        $student = StudentRecord::query()->where('student_no', 'NIS-0001')->firstOrFail();

        $create
            ->assertRedirect(route('pesantrian.students.show', $student->id))
            ->assertSessionHasNoErrors();

        self::assertSame('Nadia UI Manual', $student->full_name);
        self::assertSame($unitId, $student->primary_unit_id);
        self::assertTrue(DB::table('student_guardians')
            ->where('student_id', $student->id)
            ->where('guardian_name', 'Siti UI Manual')
            ->where('is_emergency_contact', true)
            ->exists());

        $this->actingAs($actor)->patch(route('pesantrian.students.update', $student->id), [
            'full_name' => 'Nadia UI Updated',
            'preferred_name' => null,
            'gender' => 'female',
            'birth_place' => 'Garut',
            'birth_date' => '2013-05-11',
            'previous_school' => 'SD Negeri 2',
            'primary_unit_id' => $unitId,
            'entry_date' => '2026-07-16',
            'guardian_name' => 'Siti UI Updated',
            'guardian_phone' => '089999999999',
            'guardian_relation' => 'wali',
            'is_emergency_contact' => '0',
        ])
            ->assertRedirect(route('pesantrian.students.show', $student->id))
            ->assertSessionHasNoErrors();

        self::assertTrue(DB::table('students')
            ->where('id', $student->id)
            ->where('full_name', 'Nadia UI Updated')
            ->where('birth_place', 'Garut')
            ->exists());
        self::assertTrue(DB::table('student_guardians')
            ->where('student_id', $student->id)
            ->where('guardian_name', 'Siti UI Updated')
            ->where('guardian_relation', 'wali')
            ->where('is_emergency_contact', false)
            ->exists());

        $admission = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-UI-9001',
            'candidate_name' => 'Hasan Konversi UI',
            'candidate_gender' => 'male',
            'target_unit_id' => $unitId,
            'guardian_name' => 'Abdullah Konversi',
            'guardian_relation' => 'ayah',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $actor->id,
        ]);

        $convert = $this->actingAs($actor)->post(route('pesantrian.students.from-admission'), [
            'admission_id' => $admission->id,
        ]);

        $convertedStudent = StudentRecord::query()
            ->where('admission_id', $admission->id)
            ->firstOrFail();

        $convert
            ->assertRedirect(route('pesantrian.students.show', $convertedStudent->id))
            ->assertSessionHasNoErrors();

        self::assertSame('NIS-0002', $convertedStudent->student_no);
        self::assertSame('SNTR-UI-9001', $convertedStudent->registration_no);
        self::assertSame('Hasan Konversi UI', $convertedStudent->full_name);
    }

    public function test_menolak_mutasi_web_santri_tanpa_permission_manage(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-NO-MANAGE',
            'full_name' => 'Tidak Boleh Edit',
        ]);

        $this->actingAs($actor)->post(route('pesantrian.students.store'), [
            'full_name' => 'Santri Baru',
            'guardian_name' => 'Wali Baru',
        ])->assertForbidden();

        $this->actingAs($actor)->patch(route('pesantrian.students.update', $student->id), [
            'full_name' => 'Tetap Tidak Boleh',
        ])->assertForbidden();

        $this->actingAs($actor)->post(route('pesantrian.students.from-admission'), [
            'admission_id' => (string) Str::ulid(),
        ])->assertForbidden();
    }

    public function test_mengelola_lifecycle_archive_dan_restore_santri_melalui_web_inertia(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $lifecycle = Permission::create(['name' => 'santri.lifecycle', 'guard_name' => 'web']);
        $archive = Permission::create(['name' => 'santri.archive', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo([$view, $lifecycle, $archive]);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-LIFE-001',
            'full_name' => 'Santri Lifecycle UI',
            'status' => 'active',
        ]);

        $this->actingAs($actor)->patch(route('pesantrian.students.lifecycle', $student->id), [
            'status' => 'transferred',
            'reason' => 'Pindah ke pesantren lain.',
        ])
            ->assertRedirect(route('pesantrian.students.show', $student->id))
            ->assertSessionHasNoErrors();

        self::assertTrue(DB::table('students')
            ->where('id', $student->id)
            ->where('status', 'transferred')
            ->where('status_reason', 'Pindah ke pesantren lain.')
            ->exists());

        $this->actingAs($actor)->patch(route('pesantrian.students.archive', $student->id), [
            'reason' => 'Data perlu disembunyikan dari daftar aktif.',
        ])
            ->assertRedirect(route('pesantrian.students.index'))
            ->assertSessionHasNoErrors();

        self::assertNotNull(DB::table('students')->where('id', $student->id)->value('archived_at'));

        $this->actingAs($actor)
            ->get(route('pesantrian.students.index', ['filter' => ['archived' => 'archived']]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('students.data.0.student_no', 'NIS-LIFE-001')
                ->where('students.data.0.archived_at', fn (?string $archivedAt): bool => $archivedAt !== null)
                ->where('filters.filter.archived', 'archived'));

        $this->actingAs($actor)->patch(route('pesantrian.students.restore', $student->id), [
            'reason' => 'Data kembali aktif digunakan.',
        ])
            ->assertRedirect(route('pesantrian.students.show', $student->id))
            ->assertSessionHasNoErrors();

        self::assertNull(DB::table('students')->where('id', $student->id)->value('archived_at'));
    }

    public function test_menolak_lifecycle_archive_restore_tanpa_permission_spesifik(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        $activeStudent = StudentRecord::factory()->create([
            'student_no' => 'NIS-LIFE-DENIED',
            'full_name' => 'Santri Lifecycle Ditolak',
        ]);
        $archivedStudent = StudentRecord::factory()->create([
            'student_no' => 'NIS-RESTORE-DENIED',
            'full_name' => 'Santri Restore Ditolak',
            'archived_at' => now(),
            'archived_by' => $actor->id,
        ]);

        $this->actingAs($actor)->patch(route('pesantrian.students.lifecycle', $activeStudent->id), [
            'status' => 'inactive',
            'reason' => 'Belum boleh.',
        ])->assertForbidden();

        $this->actingAs($actor)->patch(route('pesantrian.students.archive', $activeStudent->id), [
            'reason' => 'Belum boleh.',
        ])->assertForbidden();

        $this->actingAs($actor)->patch(route('pesantrian.students.restore', $archivedStudent->id), [
            'reason' => 'Belum boleh.',
        ])->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_detail_santri(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-UI-003',
            'full_name' => 'Hasan Detail',
            'status' => 'transferred',
            'status_reason' => 'Pindah pesantren.',
            'status_changed_at' => now(),
            'status_changed_by' => $actor->id,
        ]);

        StudentGuardianRecord::query()->create([
            'student_id' => $student->id,
            'guardian_name' => 'Abdullah Detail',
            'guardian_relation' => 'ayah',
            'is_primary' => true,
            'is_emergency_contact' => false,
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.students.show', $student->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Santri/pages/Show')
                ->where('student.student_no', 'NIS-UI-003')
                ->where('student.full_name', 'Hasan Detail')
                ->where('student.status', 'transferred')
                ->where('student.status_reason', 'Pindah pesantren.')
                ->where('student.primary_guardian.guardian_name', 'Abdullah Detail'));
    }

    public function test_menghubungkan_ui_santri_ke_komponen_canonical_dan_sidebar(): void
    {
        $page = $this->sourceFile('js/pages/Pesantrian/Santri/pages/Index.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/Santri/pages/Show.tsx');
        $filter = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriFilters.tsx');
        $list = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriPagination.tsx');
        $empty = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriEmptyState.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriDetailPanel.tsx');
        $mutation = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriMutationDialog.tsx');
        $conversion = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriAdmissionConversionDialog.tsx');
        $lifecycle = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriLifecycleDialog.tsx');
        $archive = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriArchiveDialog.tsx');
        $restore = $this->sourceFile('js/pages/Pesantrian/Santri/components/SantriRestoreDialog.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString("canAccess(auth, 'santri.view')", $page);
        self::assertStringContainsString('SantriSummaryCards', $page);
        self::assertStringContainsString('SantriActionBar', $page);
        self::assertStringContainsString('SantriMutationDialog', $page);
        self::assertStringContainsString('SantriAdmissionConversionDialog', $page);
        self::assertStringContainsString('SantriFilters', $page);
        self::assertStringContainsString('SantriTable', $page);
        self::assertStringContainsString('SantriPagination', $page);
        self::assertStringContainsString('Cari santri', $filter);
        self::assertStringContainsString('Status santri', $filter);
        self::assertStringContainsString('Status arsip', $filter);
        self::assertStringContainsString('Unit utama', $filter);
        self::assertStringContainsString('NIS', $list);
        self::assertStringContainsString('Wali utama', $list);
        self::assertStringContainsString('Lihat detail', $list);
        self::assertStringContainsString('Ubah status', $list);
        self::assertStringContainsString('Arsipkan', $list);
        self::assertStringContainsString('Pulihkan', $list);
        self::assertStringContainsString('Total santri', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Belum ada santri yang cocok', $empty);
        self::assertStringContainsString('Data induk santri', $detail);
        self::assertStringContainsString('Wali snapshot', $detail);
        self::assertStringContainsString('Riwayat lifecycle', $detail);
        self::assertStringContainsString('Edit data santri', $detail);
        self::assertStringContainsString('SantriMutationDialog', $detail);
        self::assertStringContainsString('Tambah santri manual', $mutation);
        self::assertStringContainsString('Wali snapshot', $mutation);
        self::assertStringContainsString('pesantrian.students.store', $mutation);
        self::assertStringContainsString('pesantrian.students.update', $mutation);
        self::assertStringContainsString('Konversi dari PPDB', $conversion);
        self::assertStringContainsString('pesantrian.students.from-admission', $conversion);
        self::assertStringContainsString('Ubah status santri', $lifecycle);
        self::assertStringContainsString('pesantrian.students.lifecycle', $lifecycle);
        self::assertStringContainsString('Arsipkan santri', $archive);
        self::assertStringContainsString('pesantrian.students.archive', $archive);
        self::assertStringContainsString('Pulihkan santri', $restore);
        self::assertStringContainsString('pesantrian.students.restore', $restore);
        self::assertStringContainsString('SantriDetailPanel', $show);
        self::assertStringContainsString('Data Induk Santri', $navigation);
        self::assertStringContainsString('pesantrian.students.index', $navigation);
        self::assertStringContainsString("'santri.view'", $navigation);
    }

    private function createOrganizationUnit(string $code, string $name): string
    {
        $id = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $id,
            'parent_id' => null,
            'code' => $code,
            'name' => $name,
            'type' => 'education_unit',
            'status' => 'active',
            'location_name' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
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
