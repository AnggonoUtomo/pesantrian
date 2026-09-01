<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
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
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString("canAccess(auth, 'santri.view')", $page);
        self::assertStringContainsString('SantriSummaryCards', $page);
        self::assertStringContainsString('SantriFilters', $page);
        self::assertStringContainsString('SantriTable', $page);
        self::assertStringContainsString('SantriPagination', $page);
        self::assertStringContainsString('Cari santri', $filter);
        self::assertStringContainsString('Status santri', $filter);
        self::assertStringContainsString('Unit utama', $filter);
        self::assertStringContainsString('NIS', $list);
        self::assertStringContainsString('Wali utama', $list);
        self::assertStringContainsString('Lihat detail', $list);
        self::assertStringContainsString('Total santri', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Belum ada santri yang cocok', $empty);
        self::assertStringContainsString('Data induk santri', $detail);
        self::assertStringContainsString('Wali snapshot', $detail);
        self::assertStringContainsString('Riwayat lifecycle', $detail);
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
