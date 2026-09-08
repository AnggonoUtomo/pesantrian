<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class KedisiplinanSantriCaseDraftMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_update_dan_submit_draft_kasus_dengan_snapshot_revision_dan_audit(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'TERLAMBAT',
            'name' => 'Terlambat Kegiatan',
            'default_severity' => 'minor',
            'default_points' => 5,
            'status' => 'active',
        ]);
        $secondCategory = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ADAB',
            'name' => 'Adab dan Ketertiban',
            'default_severity' => 'moderate',
            'default_points' => 15,
            'status' => 'active',
        ]);
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();
        $submitCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-cases.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $category->id,
            'severity' => 'minor',
            'points' => 5,
            'occurred_at' => '2026-09-21 07:30:00',
            'location' => 'Masjid',
            'description' => 'Terlambat mengikuti kegiatan pagi.',
            'assigned_employee_id' => $references['employee_id'],
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Draft kasus kedisiplinan santri berhasil dibuat.')
            ->assertJsonPath('data.case_no', 'DIS-000001')
            ->assertJsonPath('data.student_no', 'NIS-DSC-001')
            ->assertJsonPath('data.student_name', 'Ahmad Disiplin')
            ->assertJsonPath('data.unit_name', 'MTs Saka')
            ->assertJsonPath('data.category.code', 'TERLAMBAT')
            ->assertJsonPath('data.assigned_employee_name', 'Ustadz Karim Pembina')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.summary.revision_count', 1);

        $caseId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.update', $caseId), [
            'category_id' => $secondCategory->id,
            'severity' => 'moderate',
            'points' => 15,
            'description' => 'Melanggar adab kebersihan asrama.',
            'location' => 'Asrama Putra',
            'revision_reason' => 'Koreksi kategori dan kronologi.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Draft kasus kedisiplinan santri berhasil diperbarui.')
            ->assertJsonPath('data.category.code', 'ADAB')
            ->assertJsonPath('data.severity', 'moderate')
            ->assertJsonPath('data.points', 15)
            ->assertJsonPath('data.location', 'Asrama Putra')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.summary.revision_count', 2);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.submit', $caseId), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $submitCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Draft kasus kedisiplinan santri berhasil disubmit.')
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.summary.revision_count', 3);

        $createAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.updated')->firstOrFail();
        $submitAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.submitted')->firstOrFail();

        self::assertSame('KedisiplinanSantri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student_discipline_case', $createAudit->subject_type);
        self::assertSame($caseId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('DIS-000001', $createAudit->metadata['result']['case_no']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame('Koreksi kategori dan kronologi.', $updateAudit->reason);
        self::assertContains('category_id', $updateAudit->metadata['changed_fields']);
        self::assertSame('ADAB', $updateAudit->metadata['result']['category_code']);

        self::assertSame($submitCorrelationId, $submitAudit->correlation_id);
        self::assertSame(['status', 'submitted_at'], $submitAudit->metadata['changed_fields']);
        self::assertSame('submitted', $submitAudit->metadata['result']['status']);
    }

    public function test_menolak_create_draft_untuk_santri_tidak_aktif_dan_kategori_archived(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $archivedCategory = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ARSIP',
            'name' => 'Kategori Arsip',
            'status' => 'archived',
        ]);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-cases.store'), [
            'student_id' => $references['inactive_student_id'],
            'category_id' => $archivedCategory->id,
            'severity' => 'minor',
            'occurred_at' => '2026-09-21 07:30:00',
            'description' => 'Kasus tidak valid.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['student_id']);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-cases.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $archivedCategory->id,
            'severity' => 'minor',
            'occurred_at' => '2026-09-21 07:30:00',
            'description' => 'Kasus kategori arsip.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_menolak_submit_kasus_yang_bukan_draft(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create();
        $case = StudentDisciplineCaseRecord::factory()->create([
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-DSC-001',
            'student_name' => 'Ahmad Disiplin',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'submitted',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.submit', $case->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_menolak_actor_tanpa_permission_manage_untuk_mutasi_case_awal(): void
    {
        $actor = User::factory()->create();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-cases.store'), [
            'student_id' => $references['student_id'],
            'category_id' => $category->id,
            'severity' => 'minor',
            'occurred_at' => '2026-09-21 07:30:00',
            'description' => 'Tanpa akses.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function manager(): User
    {
        $manage = Permission::create(['name' => 'kedisiplinan_santri.manage', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'kedisiplinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$manage, $view]);

        return $actor;
    }

    /** @return array{student_id: string, inactive_student_id: string, unit_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $studentId = (string) Str::ulid();
        $inactiveStudentId = (string) Str::ulid();
        $unitId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MTD',
            'name' => 'MTs Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $studentId,
                'student_no' => 'NIS-DSC-001',
                'full_name' => 'Ahmad Disiplin',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveStudentId,
                'student_no' => 'NIS-DSC-099',
                'full_name' => 'Santri Nonaktif',
                'primary_unit_id' => $unitId,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-DSC-001',
            'name' => 'Ustadz Karim Pembina',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'student_id' => $studentId,
            'inactive_student_id' => $inactiveStudentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
        ];
    }
}
