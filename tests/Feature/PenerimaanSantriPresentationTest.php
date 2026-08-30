<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PenerimaanSantriPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_menolak_actor_tanpa_permission_penerimaan_santri_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('pesantrian.admissions.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_penerimaan_santri(): void
    {
        $view = Permission::create(['name' => 'penerimaan_santri.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $unitId = $this->createOrganizationUnit('MTS', 'Madrasah Tsanawiyah');

        StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-UI-001',
            'registration_period' => 'PPDB 2027',
            'candidate_name' => 'Aisyah UI',
            'candidate_gender' => 'female',
            'target_unit_id' => $unitId,
            'guardian_name' => 'Siti UI',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'pending',
            'document_checklist' => [
                ['type' => 'akta_kelahiran', 'status' => 'submitted'],
            ],
            'status' => 'submitted',
            'registered_at' => '2026-08-30 08:00:00',
        ]);
        StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-UI-002',
            'candidate_name' => 'Budi Arsip',
            'guardian_name' => 'Wali Arsip',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)
            ->get(route('pesantrian.admissions.index', [
                'search' => 'Aisyah',
                'filter' => [
                    'status' => 'submitted',
                    'target_unit_id' => $unitId,
                    'registration_fee_status' => 'pending',
                ],
                'per_page' => 10,
                'sort' => 'registration_no',
            ]))
            ->assertOk()
            ->assertSee('pesantrian.admissions.index', false)
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/PenerimaanSantri/pages/Index')
                ->where('admissions.data.0.registration_no', 'SNTR-UI-001')
                ->where('admissions.data.0.candidate_name', 'Aisyah UI')
                ->where('admissions.data.0.candidate_gender', 'female')
                ->where('admissions.data.0.target_unit_id', $unitId)
                ->where('admissions.data.0.guardian_name', 'Siti UI')
                ->where('admissions.data.0.registration_fee_status', 'pending')
                ->where('admissions.data.0.status', 'submitted')
                ->where('admissions.meta.total', 1)
                ->where('filters.search', 'Aisyah')
                ->where('filters.filter.status', 'submitted')
                ->where('filters.filter.target_unit_id', $unitId)
                ->where('filters.filter.registration_fee_status', 'pending')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'registration_no')
                ->where('targetUnitOptions.0.id', $unitId)
                ->where('targetUnitOptions.0.name', 'Madrasah Tsanawiyah')
                ->where('targetUnitOptions.0.code', 'MTS')
                ->where('pagination.defaultPerPage', 25));
    }

    public function test_menghubungkan_ui_penerimaan_santri_ke_komponen_canonical_dan_permission(): void
    {
        $page = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/pages/Index.tsx');
        $filter = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/components/AdmissionFilterForm.tsx');
        $list = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/components/AdmissionList.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/components/AdmissionPagination.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/components/AdmissionSummary.tsx');
        $empty = $this->sourceFile('js/pages/Pesantrian/PenerimaanSantri/components/AdmissionEmptyState.tsx');

        self::assertStringContainsString("canAccess(auth, 'penerimaan_santri.view')", $page);
        self::assertStringContainsString('AdmissionSummary', $page);
        self::assertStringContainsString('AdmissionFilterForm', $page);
        self::assertStringContainsString('AdmissionList', $page);
        self::assertStringContainsString('AdmissionPagination', $page);
        self::assertStringContainsString('Cari calon santri', $filter);
        self::assertStringContainsString('Status pendaftaran', $filter);
        self::assertStringContainsString('Unit tujuan', $filter);
        self::assertStringContainsString('Status biaya', $filter);
        self::assertStringContainsString('Nomor pendaftaran', $list);
        self::assertStringContainsString('Wali', $list);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Total pendaftaran', $summary);
        self::assertStringContainsString('Belum ada pendaftaran yang cocok', $empty);
    }

    private function createOrganizationUnit(string $code, string $name): string
    {
        $id = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'type' => 'education_unit',
            'status' => 'active',
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
