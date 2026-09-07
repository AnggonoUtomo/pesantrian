<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TahfidzProgramTargetMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_dan_memperbarui_program_tahfidz_dengan_audit(): void
    {
        $actor = $this->manager();
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.programs.store'), [
            'code' => 'THF-REG',
            'name' => 'Tahfidz Reguler',
            'description' => 'Program hafalan dasar.',
            'status' => 'active',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Program tahfidz berhasil dibuat.')
            ->assertJsonPath('data.code', 'THF-REG')
            ->assertJsonPath('data.status', 'active');

        $programId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.programs.update', $programId), [
            'name' => 'Tahfidz Reguler Revisi',
            'status' => 'inactive',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Program tahfidz berhasil diperbarui.')
            ->assertJsonPath('data.name', 'Tahfidz Reguler Revisi')
            ->assertJsonPath('data.status', 'inactive');

        $createAudit = AuditRecord::query()->where('action', 'tahfidz.program.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'tahfidz.program.updated')->firstOrFail();

        self::assertSame('Tahfidz', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('tahfidz_program', $createAudit->subject_type);
        self::assertSame($programId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('THF-REG', $createAudit->metadata['result']['code']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame(['name', 'status'], $updateAudit->metadata['changed_fields']);
        self::assertSame('Tahfidz Reguler Revisi', $updateAudit->metadata['result']['name']);
    }

    public function test_membuat_dan_memperbarui_target_hafalan_dengan_snapshot_santri_periode_dan_audit(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create([
            'code' => 'THF-INT',
            'name' => 'Tahfidz Intensif',
        ]);
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.targets.store'), [
            'program_id' => $program->id,
            'student_id' => $references['active_student_id'],
            'academic_period_id' => $references['academic_term_id'],
            'target_juz' => 1,
            'target_surah' => 'Al-Baqarah',
            'target_ayah_from' => 1,
            'target_ayah_to' => 20,
            'target_note' => 'Target awal semester.',
            'status' => 'active',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('message', 'Target hafalan berhasil dibuat.')
            ->assertJsonPath('data.student_no', 'NIS-THF-001')
            ->assertJsonPath('data.student_name', 'Ahmad Hafidz')
            ->assertJsonPath('data.period_label', 'Semester Ganjil 2026/2027 Tahun Ajaran 2026/2027')
            ->assertJsonPath('data.target_juz', 1)
            ->assertJsonPath('data.status', 'active');

        $targetId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.targets.update', $targetId), [
            'student_id' => $references['second_student_id'],
            'target_juz' => 2,
            'target_note' => 'Naik target setelah evaluasi.',
            'status' => 'completed',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Target hafalan berhasil diperbarui.')
            ->assertJsonPath('data.student_no', 'NIS-THF-002')
            ->assertJsonPath('data.student_name', 'Budi Murojaah')
            ->assertJsonPath('data.target_juz', 2)
            ->assertJsonPath('data.status', 'completed');

        $createAudit = AuditRecord::query()->where('action', 'tahfidz.target.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'tahfidz.target.updated')->firstOrFail();

        self::assertSame('Tahfidz', $createAudit->module);
        self::assertSame('tahfidz_target', $createAudit->subject_type);
        self::assertSame($targetId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('NIS-THF-001', $createAudit->metadata['result']['student_no']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertContains('student_no', $updateAudit->metadata['changed_fields']);
        self::assertSame('NIS-THF-002', $updateAudit->metadata['result']['student_no']);
    }

    public function test_menolak_target_untuk_santri_tidak_aktif(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.targets.store'), [
            'program_id' => $program->id,
            'student_id' => $references['inactive_student_id'],
            'target_juz' => 1,
            'status' => 'active',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID')
            ->assertJsonValidationErrors(['student_id']);
    }

    public function test_menolak_actor_tanpa_permission_tahfidz_manage(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.programs.store'), [
            'code' => 'THF-NOAUTH',
            'name' => 'Tahfidz Tanpa Akses',
            'status' => 'active',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function manager(): User
    {
        $permission = Permission::create(['name' => 'tahfidz.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($permission);

        return $actor;
    }

    /** @return array{active_student_id: string, second_student_id: string, inactive_student_id: string, academic_term_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $activeStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();
        $inactiveStudentId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicTermId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-THF',
            'name' => 'MA Tahfidz',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $activeStudentId,
                'student_no' => 'NIS-THF-001',
                'full_name' => 'Ahmad Hafidz',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-THF-002',
                'full_name' => 'Budi Murojaah',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveStudentId,
                'student_no' => 'NIS-THF-003',
                'full_name' => 'Calon Nonaktif',
                'primary_unit_id' => $unitId,
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
            'id' => $academicTermId,
            'academic_year_id' => $academicYearId,
            'code' => '2026-1',
            'name' => 'Semester Ganjil 2026/2027',
            'sequence' => 1,
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'active_student_id' => $activeStudentId,
            'second_student_id' => $secondStudentId,
            'inactive_student_id' => $inactiveStudentId,
            'academic_term_id' => $academicTermId,
        ];
    }
}
