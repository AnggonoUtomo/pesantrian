<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PrestasiSantriDraftMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_dan_update_draft_prestasi_dengan_snapshot_revision_dan_audit(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'AKD',
            'name' => 'Akademik',
            'status' => 'active',
        ]);
        $secondCategory = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'THF',
            'name' => 'Prestasi Tahfidz',
            'status' => 'active',
        ]);
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $category->id,
            'academic_period_id' => $references['academic_period_id'],
            'mentor_employee_id' => $references['employee_id'],
            'title' => 'Juara Olimpiade Matematika',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'organizer' => 'Kemenag Provinsi',
            'event_name' => 'Olimpiade Matematika Madrasah',
            'event_location' => 'Bandung',
            'achieved_on' => '2026-09-17',
            'description' => 'Santri meraih juara pertama tingkat provinsi.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Draft prestasi santri berhasil dibuat.')
            ->assertJsonPath('data.achievement_no', 'PRS-000001')
            ->assertJsonPath('data.student_no', 'NIS-PRS-001')
            ->assertJsonPath('data.student_name', 'Ahmad Prestasi')
            ->assertJsonPath('data.category.code', 'AKD')
            ->assertJsonPath('data.academic_period_label', 'Tahun Ajaran 2026/2027 Semester Ganjil')
            ->assertJsonPath('data.mentor_name', 'Ustadz Karim Pembina')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.summary.revision_count', 1);

        $achievementId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.update', $achievementId), [
            'category_id' => $secondCategory->id,
            'title' => 'Juara Musabaqah Hifzil Quran',
            'level' => 'national',
            'result' => 'Juara 2',
            'notes' => 'Masuk arsip prestasi unggulan.',
            'revision_reason' => 'Koreksi kategori dan hasil prestasi.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Draft prestasi santri berhasil diperbarui.')
            ->assertJsonPath('data.category.code', 'THF')
            ->assertJsonPath('data.title', 'Juara Musabaqah Hifzil Quran')
            ->assertJsonPath('data.level', 'national')
            ->assertJsonPath('data.result', 'Juara 2')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.summary.revision_count', 2);

        $createAudit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.updated')->firstOrFail();

        self::assertSame('PrestasiSantri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student_achievement', $createAudit->subject_type);
        self::assertSame($achievementId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('PRS-000001', $createAudit->metadata['result']['achievement_no']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame('Koreksi kategori dan hasil prestasi.', $updateAudit->reason);
        self::assertContains('category_id', $updateAudit->metadata['changed_fields']);
        self::assertSame('THF', $updateAudit->metadata['result']['category_code']);
    }

    public function test_menolak_create_draft_untuk_santri_tidak_aktif_kategori_archived_dan_pembina_tidak_aktif(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $archivedCategory = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'ARSIP',
            'name' => 'Kategori Arsip',
            'status' => 'archived',
        ]);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.store'), [
            'student_id' => $references['inactive_student_id'],
            'category_id' => $archivedCategory->id,
            'mentor_employee_id' => $references['inactive_employee_id'],
            'title' => 'Prestasi Tidak Valid',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'achieved_on' => '2026-09-17',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PRESTASI_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['student_id']);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $archivedCategory->id,
            'title' => 'Prestasi Kategori Arsip',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'achieved_on' => '2026-09-17',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);

        $activeCategory = StudentAchievementCategoryRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $activeCategory->id,
            'mentor_employee_id' => $references['inactive_employee_id'],
            'title' => 'Prestasi Pembina Tidak Aktif',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'achieved_on' => '2026-09-17',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mentor_employee_id']);
    }

    public function test_menolak_update_prestasi_yang_bukan_draft(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = StudentAchievementRecord::factory()->create([
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-PRS-001',
            'student_name' => 'Ahmad Prestasi',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'verified',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.update', $achievement->id), [
            'title' => 'Tidak Boleh Edit',
            'revision_reason' => 'Coba update data final.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PRESTASI_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_menolak_actor_tanpa_permission_record_untuk_mutasi_draft(): void
    {
        $actor = User::factory()->create();
        $references = $this->seedReferences();
        $category = StudentAchievementCategoryRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $category->id,
            'title' => 'Tanpa Akses',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'achieved_on' => '2026-09-17',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function recorder(): User
    {
        $record = Permission::create(['name' => 'prestasi_santri.record', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$record, $view]);

        return $actor;
    }

    /** @return array{student_id: string, inactive_student_id: string, unit_id: string, employee_id: string, inactive_employee_id: string, academic_period_id: string} */
    private function seedReferences(): array
    {
        $studentId = (string) Str::ulid();
        $inactiveStudentId = (string) Str::ulid();
        $unitId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();
        $inactiveEmployeeId = (string) Str::ulid();
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
                'id' => $studentId,
                'student_no' => 'NIS-PRS-001',
                'full_name' => 'Ahmad Prestasi',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveStudentId,
                'student_no' => 'NIS-PRS-099',
                'full_name' => 'Santri Nonaktif',
                'primary_unit_id' => $unitId,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employees')->insert([
            [
                'id' => $employeeId,
                'employee_no' => 'PEG-PRS-001',
                'name' => 'Ustadz Karim Pembina',
                'employment_type' => 'staff',
                'position' => 'Pembina Prestasi',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveEmployeeId,
                'employee_no' => 'PEG-PRS-099',
                'name' => 'Pegawai Nonaktif',
                'employment_type' => 'staff',
                'position' => 'Pembina Lama',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
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
            'student_id' => $studentId,
            'inactive_student_id' => $inactiveStudentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
            'inactive_employee_id' => $inactiveEmployeeId,
            'academic_period_id' => $academicPeriodId,
        ];
    }
}
