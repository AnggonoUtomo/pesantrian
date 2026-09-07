<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PerizinanSantriDecisionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_menyetujui_perizinan_submitted_dengan_revision_dan_audit(): void
    {
        $actor = $this->actor(['perizinan_santri.approve']);
        $permit = $this->permit('submitted');
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.approve', $permit->id), [
                'review_note' => 'Izin disetujui oleh pengasuhan.',
            ], [
                'Idempotency-Key' => (string) Str::ulid(),
                'X-Correlation-ID' => $correlationId,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Permohonan izin santri berhasil disetujui.')
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.reviewed_by', $actor->id)
            ->assertJsonPath('data.review_note', 'Izin disetujui oleh pengasuhan.')
            ->assertJsonPath('data.summary.is_active', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', 'Izin disetujui oleh pengasuhan.')
            ->assertJsonPath('data.revisions.0.summary.action', 'review')
            ->assertJsonPath('data.revisions.0.summary.to_status', 'approved');

        $this->assertDatabaseHas('student_permits', [
            'id' => $permit->id,
            'status' => 'approved',
            'reviewed_by' => $actor->id,
            'review_note' => 'Izin disetujui oleh pengasuhan.',
        ]);

        $audit = AuditRecord::query()->where('action', 'perizinan_santri.permit.approved')->firstOrFail();

        self::assertSame('PerizinanSantri', $audit->module);
        self::assertSame('student_permit', $audit->subject_type);
        self::assertSame($permit->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('submitted', $audit->metadata['from_status']);
        self::assertSame('approved', $audit->metadata['to_status']);
        self::assertSame('approved', $audit->metadata['result']['status']);
    }

    public function test_reject_menolak_perizinan_submitted_dengan_alasan_revision_dan_audit(): void
    {
        $actor = $this->actor(['perizinan_santri.approve']);
        $permit = $this->permit('submitted');
        $reason = 'Data izin belum lengkap dan wali belum terkonfirmasi.';

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.reject', $permit->id), [
                'reason' => $reason,
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('message', 'Permohonan izin santri berhasil ditolak.')
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.reviewed_by', $actor->id)
            ->assertJsonPath('data.review_note', $reason)
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', $reason)
            ->assertJsonPath('data.revisions.0.summary.to_status', 'rejected');

        $this->assertDatabaseHas('student_permits', [
            'id' => $permit->id,
            'status' => 'rejected',
            'reviewed_by' => $actor->id,
            'review_note' => $reason,
        ]);

        expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
            ->toContain('perizinan_santri.permit.rejected');
    }

    public function test_approve_dan_reject_hanya_dari_submitted_serta_reject_wajib_alasan(): void
    {
        $actor = $this->actor(['perizinan_santri.approve']);
        $draft = $this->permit('draft');
        $approved = $this->permit('approved');
        $submitted = $this->permit('submitted');

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.approve', $draft->id), [
                'review_note' => 'Belum boleh approve draft.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.reject', $approved->id), [
                'reason' => 'Tidak boleh reject status final/approved.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.reject', $submitted->id), [
                'reason' => 'x',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_menolak_actor_tanpa_permission_approve_untuk_decision(): void
    {
        $actor = User::factory()->create();
        $permit = $this->permit('submitted');

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.approve', $permit->id), [
                'review_note' => 'Tidak punya akses.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.reject', $permit->id), [
                'reason' => 'Tidak punya akses.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
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

    private function permit(string $status): StudentPermitRecord
    {
        $student = $this->student();

        return StudentPermitRecord::factory()->create([
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => $student['full_name'],
            'permit_type' => 'home_visit',
            'starts_at' => '2026-09-20 08:00:00',
            'ends_at' => '2026-09-20 17:00:00',
            'destination' => 'Rumah wali',
            'reason' => 'Keperluan keluarga.',
            'status' => $status,
            'submitted_at' => $status === 'draft' ? null : '2026-09-19 08:00:00',
        ]);
    }

    /** @return array{id: string, student_no: string, full_name: string} */
    private function student(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $studentNo = 'NIS-DEC-'.substr($studentId, -6);
        $fullName = 'Ahmad Decision Perizinan';

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-'.substr($unitId, -6),
            'name' => 'MA Decision Perizinan',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => $studentNo,
            'full_name' => $fullName,
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $studentId,
            'student_no' => $studentNo,
            'full_name' => $fullName,
        ];
    }
}
