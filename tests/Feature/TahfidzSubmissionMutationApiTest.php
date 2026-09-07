<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TahfidzSubmissionMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_setoran_hafalan_baru_dengan_snapshot_santri_pembimbing_dan_audit(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create(['code' => 'THF-SETOR']);
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-SET-001',
            'student_name' => 'Ahmad Setoran',
            'status' => 'active',
        ]);
        $correlationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['first_student_id'],
            'supervisor_id' => $references['active_employee_id'],
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'surah' => 'Al-Baqarah',
            'ayah_from' => 1,
            'ayah_to' => 10,
            'status' => 'submitted',
            'quality_note' => 'Setoran awal lancar.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Setoran tahfidz berhasil dibuat.')
            ->assertJsonPath('data.student_no', 'NIS-SET-001')
            ->assertJsonPath('data.student_name', 'Ahmad Setoran')
            ->assertJsonPath('data.supervisor_name', 'Ustadz Tahfidz Aktif')
            ->assertJsonPath('data.type', 'new_memorization')
            ->assertJsonPath('data.status', 'submitted');

        $submissionId = (string) $created->json('data.id');

        $this->assertDatabaseHas('tahfidz_submissions', [
            'id' => $submissionId,
            'created_by' => $actor->id,
            'student_no' => 'NIS-SET-001',
            'student_name' => 'Ahmad Setoran',
            'supervisor_name' => 'Ustadz Tahfidz Aktif',
        ]);

        $audit = AuditRecord::query()->where('action', 'tahfidz.submission.created')->firstOrFail();

        self::assertSame('Tahfidz', $audit->module);
        self::assertSame('tahfidz_submission', $audit->subject_type);
        self::assertSame($submissionId, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('NIS-SET-001', $audit->metadata['result']['student_no']);
        self::assertSame('Ustadz Tahfidz Aktif', $audit->metadata['result']['supervisor_name']);
    }

    public function test_membuat_murojaah_dan_memperbarui_setoran_draft_atau_submitted(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create(['code' => 'THF-MUR']);
        $submission = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-SET-001',
            'student_name' => 'Ahmad Setoran',
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'student_id' => $references['second_student_id'],
            'supervisor_id' => $references['active_employee_id'],
            'submission_date' => '2026-09-08',
            'type' => 'murojaah',
            'surah' => 'Ali Imran',
            'ayah_from' => 1,
            'ayah_to' => 5,
            'status' => 'draft',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertCreated()
            ->assertJsonPath('data.student_no', 'NIS-SET-002')
            ->assertJsonPath('data.type', 'murojaah')
            ->assertJsonPath('data.status', 'draft');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.update', $submission->id), [
            'student_id' => $references['second_student_id'],
            'supervisor_id' => $references['active_employee_id'],
            'submission_date' => '2026-09-09',
            'type' => 'murojaah',
            'juz' => 2,
            'status' => 'submitted',
            'quality_note' => 'Diubah menjadi murojaah.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('message', 'Setoran tahfidz berhasil diperbarui.')
            ->assertJsonPath('data.student_no', 'NIS-SET-002')
            ->assertJsonPath('data.supervisor_name', 'Ustadz Tahfidz Aktif')
            ->assertJsonPath('data.type', 'murojaah')
            ->assertJsonPath('data.status', 'submitted');

        expect(AuditRecord::query()->where('module', 'Tahfidz')->pluck('action')->all())
            ->toContain('tahfidz.submission.created')
            ->toContain('tahfidz.submission.updated');
    }

    public function test_menolak_range_ayat_tidak_valid_dan_target_yang_tidak_sesuai(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create();
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-SET-002',
            'student_name' => 'Budi Murojaah',
            'status' => 'active',
        ]);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['first_student_id'],
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'ayah_from' => 20,
            'ayah_to' => 10,
            'status' => 'submitted',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target_id', 'ayah_to']);
    }

    public function test_menolak_santri_atau_pembimbing_tidak_aktif(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'student_id' => $references['inactive_student_id'],
            'supervisor_id' => $references['active_employee_id'],
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'status' => 'submitted',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID')
            ->assertJsonValidationErrors(['student_id']);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'supervisor_id' => $references['inactive_employee_id'],
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'status' => 'submitted',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID')
            ->assertJsonValidationErrors(['supervisor_id']);
    }

    public function test_menolak_update_langsung_setoran_accepted_atau_void(): void
    {
        $actor = $this->recorder();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create();
        $accepted = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-SET-001',
            'student_name' => 'Ahmad Setoran',
            'status' => 'accepted',
        ]);
        $void = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-SET-001',
            'student_name' => 'Ahmad Setoran',
            'status' => 'void',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.update', $accepted->id), [
            'quality_note' => 'Tidak boleh update langsung.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.update', $void->id), [
            'quality_note' => 'Tidak boleh update langsung.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID');
    }

    public function test_menolak_actor_tanpa_permission_tahfidz_record(): void
    {
        $actor = User::factory()->create();
        $program = TahfidzProgramRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.tahfidz.submissions.store'), [
            'program_id' => $program->id,
            'student_id' => (string) Str::ulid(),
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'status' => 'submitted',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function recorder(): User
    {
        $permission = Permission::create(['name' => 'tahfidz.record', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($permission);

        return $actor;
    }

    /** @return array{first_student_id: string, second_student_id: string, inactive_student_id: string, active_employee_id: string, inactive_employee_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();
        $inactiveStudentId = (string) Str::ulid();
        $activeEmployeeId = (string) Str::ulid();
        $inactiveEmployeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-SET',
            'name' => 'MA Setoran',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-SET-001',
                'full_name' => 'Ahmad Setoran',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-SET-002',
                'full_name' => 'Budi Murojaah',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveStudentId,
                'student_no' => 'NIS-SET-003',
                'full_name' => 'Cici Nonaktif',
                'primary_unit_id' => $unitId,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employees')->insert([
            [
                'id' => $activeEmployeeId,
                'primary_unit_id' => $unitId,
                'employee_no' => 'PEG-SET-001',
                'name' => 'Ustadz Tahfidz Aktif',
                'employment_type' => 'teacher',
                'position' => 'Pembimbing Tahfidz',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveEmployeeId,
                'primary_unit_id' => $unitId,
                'employee_no' => 'PEG-SET-002',
                'name' => 'Ustadz Nonaktif',
                'employment_type' => 'teacher',
                'position' => 'Pembimbing Tahfidz',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return [
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
            'inactive_student_id' => $inactiveStudentId,
            'active_employee_id' => $activeEmployeeId,
            'inactive_employee_id' => $inactiveEmployeeId,
        ];
    }
}
