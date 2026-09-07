<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TahfidzLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_menerima_setoran_submitted_dengan_revision_history_dan_audit(): void
    {
        $actor = $this->actor(['tahfidz.review']);
        $submission = $this->submission('submitted');
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $submission->id), [
            'status' => 'accepted',
            'reason' => 'Hafalan lancar dan siap diterima.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Setoran tahfidz berhasil direview.')
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.reviewed_by', $actor->id)
            ->assertJsonPath('data.summary.has_revision', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', 'Hafalan lancar dan siap diterima.')
            ->assertJsonPath('data.revisions.0.summary.action', 'review')
            ->assertJsonPath('data.revisions.0.summary.to_status', 'accepted');

        $this->assertDatabaseHas('tahfidz_submissions', [
            'id' => $submission->id,
            'status' => 'accepted',
            'reviewed_by' => $actor->id,
        ]);
        $this->assertDatabaseHas('tahfidz_submission_revisions', [
            'submission_id' => $submission->id,
            'reason' => 'Hafalan lancar dan siap diterima.',
            'changed_by' => $actor->id,
        ]);

        $audit = AuditRecord::query()->where('action', 'tahfidz.submission.reviewed')->firstOrFail();

        self::assertSame('Tahfidz', $audit->module);
        self::assertSame('tahfidz_submission', $audit->subject_type);
        self::assertSame($submission->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('accepted', $audit->metadata['to_status']);
        self::assertSame(1, $audit->metadata['result']['revision_count']);
    }

    public function test_review_meminta_revisi_setoran_submitted(): void
    {
        $actor = $this->actor(['tahfidz.review']);
        $submission = $this->submission('submitted');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $submission->id), [
            'status' => 'needs_revision',
            'reason' => 'Ayat akhir perlu diulang besok.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('data.status', 'needs_revision')
            ->assertJsonPath('data.revisions.0.summary.to_status', 'needs_revision');
    }

    public function test_review_menolak_draft_atau_final_dan_mewajibkan_alasan(): void
    {
        $actor = $this->actor(['tahfidz.review']);
        $draft = $this->submission('draft');
        $accepted = $this->submission('accepted');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $draft->id), [
            'status' => 'accepted',
            'reason' => 'Belum boleh direview.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $accepted->id), [
            'status' => 'accepted',
            'reason' => 'Sudah final.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $draft->id), [
            'status' => 'accepted',
            'reason' => 'x',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_void_membatalkan_setoran_tanpa_menghapus_data_dengan_revision_history_dan_audit(): void
    {
        $actor = $this->actor(['tahfidz.archive']);
        $submission = $this->submission('submitted');
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.void', $submission->id), [
            'reason' => 'Setoran salah input tanggal dan dibuat ulang.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Setoran tahfidz berhasil dibatalkan.')
            ->assertJsonPath('data.status', 'void')
            ->assertJsonPath('data.voided_by', $actor->id)
            ->assertJsonPath('data.void_reason', 'Setoran salah input tanggal dan dibuat ulang.')
            ->assertJsonPath('data.summary.has_revision', true)
            ->assertJsonPath('data.revisions.0.summary.action', 'void');

        $this->assertDatabaseHas('tahfidz_submissions', [
            'id' => $submission->id,
            'status' => 'void',
            'voided_by' => $actor->id,
            'void_reason' => 'Setoran salah input tanggal dan dibuat ulang.',
        ]);
        $this->assertDatabaseHas('tahfidz_submission_revisions', [
            'submission_id' => $submission->id,
            'reason' => 'Setoran salah input tanggal dan dibuat ulang.',
            'changed_by' => $actor->id,
        ]);

        self::assertSame(1, TahfidzSubmissionRecord::query()->whereKey($submission->id)->count());

        $audit = AuditRecord::query()->where('action', 'tahfidz.submission.voided')->firstOrFail();

        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('void', $audit->metadata['to_status']);
        self::assertSame(1, $audit->metadata['result']['revision_count']);
    }

    public function test_void_mewajibkan_alasan_dan_menolak_void_ulang(): void
    {
        $actor = $this->actor(['tahfidz.archive']);
        $submitted = $this->submission('submitted');
        $void = $this->submission('void');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.void', $submitted->id), [
            'reason' => 'x',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.void', $void->id), [
            'reason' => 'Tidak boleh void ulang.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'TAHFIDZ_MUTATION_INVALID');
    }

    public function test_menolak_actor_tanpa_permission_lifecycle_tahfidz(): void
    {
        $actor = User::factory()->create();
        $submission = $this->submission('submitted');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.review', $submission->id), [
            'status' => 'accepted',
            'reason' => 'Tidak punya akses.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.tahfidz.submissions.void', $submission->id), [
            'reason' => 'Tidak punya akses.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    /** @param list<string> $permissions */
    private function actor(array $permissions): User
    {
        $actor = User::factory()->create();

        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::create(['name' => $permission, 'guard_name' => 'web']));
        }

        return $actor;
    }

    private function submission(string $status): TahfidzSubmissionRecord
    {
        $program = TahfidzProgramRecord::factory()->create();
        $student = $this->student();

        return TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => $student['full_name'],
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'surah' => 'Al-Baqarah',
            'ayah_from' => 1,
            'ayah_to' => 10,
            'status' => $status,
        ]);
    }

    /** @return array{id: string, student_no: string, full_name: string} */
    private function student(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $studentNo = 'NIS-LIFE-'.substr($studentId, -6);
        $fullName = 'Ahmad Lifecycle Tahfidz';

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-'.substr($unitId, -6),
            'name' => 'MA Lifecycle Tahfidz',
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
