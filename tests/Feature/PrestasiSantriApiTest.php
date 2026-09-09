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
use Illuminate\Support\Str;
use Tests\TestCase;

final class PrestasiSantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_kategori_prestasi(): void
    {
        $actor = $this->viewer();

        $active = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'AKD',
            'name' => 'Akademik',
            'description' => 'Prestasi lomba akademik santri.',
            'status' => 'active',
        ]);
        StudentAchievementCategoryRecord::factory()->create([
            'code' => 'ARSIP',
            'name' => 'Kategori Arsip',
            'status' => 'archived',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.categories.index').'?'.http_build_query([
                'search' => 'akad',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar kategori prestasi santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.code', 'AKD')
            ->assertJsonPath('data.0.name', 'Akademik')
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'code',
                    'name',
                    'description',
                    'status',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['correlation_id'],
            ]);
    }

    public function test_mengembalikan_list_prestasi_dengan_filter_search_pagination_sort_dan_envelope_canonical(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'AKD',
            'name' => 'Akademik',
        ]);

        $target = StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-API-001',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-PRS-001',
            'student_name' => 'Ahmad Prestasi',
            'academic_period_id' => $references['academic_period_id'],
            'academic_period_label' => '2026/2027 Semester Ganjil',
            'mentor_employee_id' => $references['employee_id'],
            'mentor_name' => 'Ustadz Karim Pembina',
            'title' => 'Juara Olimpiade Matematika',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'organizer' => 'Kemenag Provinsi',
            'event_name' => 'Olimpiade Matematika Madrasah',
            'event_location' => 'Bandung',
            'achieved_on' => '2026-09-17',
            'description' => 'Santri meraih juara pertama tingkat provinsi.',
            'status' => 'verified',
            'submitted_at' => '2026-09-17 08:00:00',
            'verified_at' => '2026-09-17 09:00:00',
            'verified_by' => $actor->id,
            'verification_note' => 'Sertifikat sudah dicek oleh admin.',
        ]);
        StudentAchievementRevisionRecord::factory()->create([
            'achievement_id' => $target->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'reason' => 'Prestasi diverifikasi.',
            'changed_at' => '2026-09-17 09:00:00',
            'summary' => ['changed_fields' => ['status'], 'to_status' => 'verified'],
        ]);

        StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-API-002',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-PRS-002',
            'student_name' => 'Budi Prestasi',
            'academic_period_id' => $references['academic_period_id'],
            'mentor_employee_id' => null,
            'title' => 'Finalis Lomba Pidato',
            'level' => 'district',
            'result' => 'Finalis',
            'achieved_on' => '2026-10-01',
            'status' => 'submitted',
        ]);

        $query = http_build_query([
            'search' => 'Ahmad',
            'filter' => [
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-30',
                'status' => 'verified',
                'level' => 'province',
                'category_id' => $category->id,
                'student_id' => $references['first_student_id'],
                'mentor_employee_id' => $references['employee_id'],
                'academic_period_id' => $references['academic_period_id'],
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'achieved_on',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar prestasi santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.achievement_no', 'PRS-API-001')
            ->assertJsonPath('data.0.student_no', 'NIS-PRS-001')
            ->assertJsonPath('data.0.student_name', 'Ahmad Prestasi')
            ->assertJsonPath('data.0.category.name', 'Akademik')
            ->assertJsonPath('data.0.academic_period_label', '2026/2027 Semester Ganjil')
            ->assertJsonPath('data.0.mentor_name', 'Ustadz Karim Pembina')
            ->assertJsonPath('data.0.level', 'province')
            ->assertJsonPath('data.0.result', 'Juara 1')
            ->assertJsonPath('data.0.status', 'verified')
            ->assertJsonPath('data.0.summary.is_final', true)
            ->assertJsonPath('data.0.summary.revision_count', 1)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'achievement_no',
                    'student_id',
                    'student_no',
                    'student_name',
                    'category',
                    'academic_period_id',
                    'academic_period_label',
                    'mentor_employee_id',
                    'mentor_name',
                    'title',
                    'achievement_type',
                    'level',
                    'result',
                    'status',
                    'summary',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_prestasi_dengan_snapshot_verifikasi_dan_revisions(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'THF',
            'name' => 'Prestasi Tahfidz',
        ]);

        $achievement = StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-API-003',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-PRS-001',
            'student_name' => 'Ahmad Prestasi',
            'academic_period_id' => $references['academic_period_id'],
            'academic_period_label' => '2026/2027 Semester Ganjil',
            'mentor_employee_id' => $references['employee_id'],
            'mentor_name' => 'Ustadz Karim Pembina',
            'title' => 'Juara Musabaqah Hifzil Quran',
            'achievement_type' => 'competition',
            'level' => 'national',
            'result' => 'Juara 2',
            'organizer' => 'Forum Pesantren Nasional',
            'event_name' => 'MHQ Nasional',
            'event_location' => 'Jakarta',
            'achieved_on' => '2026-09-18',
            'period_started_on' => '2026-09-15',
            'period_ended_on' => '2026-09-18',
            'description' => 'Prestasi lomba hafalan tingkat nasional.',
            'notes' => 'Masuk arsip prestasi unggulan.',
            'status' => 'needs_revision',
            'submitted_at' => '2026-09-18 08:00:00',
            'verified_at' => null,
            'verification_note' => 'Mohon lengkapi nama penyelenggara resmi.',
        ]);
        StudentAchievementRevisionRecord::factory()->create([
            'achievement_id' => $achievement->id,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'reason' => 'Catatan prestasi disubmit.',
            'changed_at' => '2026-09-18 08:00:00',
            'summary' => ['changed_fields' => ['status'], 'to_status' => 'submitted'],
        ]);
        StudentAchievementRevisionRecord::factory()->create([
            'achievement_id' => $achievement->id,
            'from_status' => 'submitted',
            'to_status' => 'needs_revision',
            'reason' => 'Butuh revisi data penyelenggara.',
            'changed_at' => '2026-09-18 09:00:00',
            'summary' => ['changed_fields' => ['status', 'verification_note'], 'to_status' => 'needs_revision'],
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.show', $achievement->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail prestasi santri berhasil dibaca.')
            ->assertJsonPath('data.id', $achievement->id)
            ->assertJsonPath('data.achievement_no', 'PRS-API-003')
            ->assertJsonPath('data.student_name', 'Ahmad Prestasi')
            ->assertJsonPath('data.category.code', 'THF')
            ->assertJsonPath('data.academic_period_label', '2026/2027 Semester Ganjil')
            ->assertJsonPath('data.mentor_name', 'Ustadz Karim Pembina')
            ->assertJsonPath('data.title', 'Juara Musabaqah Hifzil Quran')
            ->assertJsonPath('data.level', 'national')
            ->assertJsonPath('data.result', 'Juara 2')
            ->assertJsonPath('data.status', 'needs_revision')
            ->assertJsonPath('data.verification_note', 'Mohon lengkapi nama penyelenggara resmi.')
            ->assertJsonPath('data.summary.is_final', false)
            ->assertJsonPath('data.summary.needs_action', true)
            ->assertJsonPath('data.summary.revision_count', 2)
            ->assertJsonPath('data.revisions.0.reason', 'Catatan prestasi disubmit.')
            ->assertJsonPath('data.revisions.1.summary.to_status', 'needs_revision');
    }

    public function test_menolak_actor_tanpa_permission_prestasi_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.index'))
            ->assertForbidden();
    }

    public function test_mengembalikan_404_untuk_detail_prestasi_yang_tidak_ada(): void
    {
        $actor = $this->viewer();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.show', (string) Str::ulid()))
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        return $actor;
    }

    /** @return array{first_student_id: string, second_student_id: string, unit_id: string, employee_id: string, academic_period_id: string} */
    private function seedReferences(): array
    {
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();
        $unitId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicPeriodId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MTP',
            'name' => 'MTs Prestasi',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-PRS-001',
                'full_name' => 'Ahmad Prestasi',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-PRS-002',
                'full_name' => 'Budi Prestasi',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-PRS-101',
            'name' => 'Ustadz Karim Pembina',
            'employment_type' => 'staff',
            'position' => 'Pembina Prestasi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_years')->insert([
            'id' => $academicYearId,
            'code' => '2026-2027',
            'name' => 'Tahun Ajaran 2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_terms')->insert([
            'id' => $academicPeriodId,
            'academic_year_id' => $academicYearId,
            'code' => 'GANJIL',
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
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
            'academic_period_id' => $academicPeriodId,
        ];
    }
}
