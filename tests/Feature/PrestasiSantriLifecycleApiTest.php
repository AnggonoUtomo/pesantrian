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

final class PrestasiSantriLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_verify_dan_request_revision_mencatat_status_revision_dan_audit(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create(['code' => 'AKD', 'name' => 'Akademik']);
        $draft = $this->achievement($category, 'draft', 'PRS-LC-001');
        $submittedForRevision = $this->achievement($category, 'submitted', 'PRS-LC-002');
        $submitCorrelationId = (string) Str::ulid();
        $verifyCorrelationId = (string) Str::ulid();
        $revisionCorrelationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.submit', $draft->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $submitCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Draft prestasi santri berhasil disubmit.')
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.submitted_by', $actor->id)
            ->assertJsonPath('data.summary.needs_action', true)
            ->assertJsonPath('data.summary.revision_count', 1);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.verify', $draft->id), [
            'verification_note' => 'Sertifikat dan data lomba sudah sesuai.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $verifyCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Prestasi santri berhasil diverifikasi.')
            ->assertJsonPath('data.status', 'verified')
            ->assertJsonPath('data.verified_by', $actor->id)
            ->assertJsonPath('data.verification_note', 'Sertifikat dan data lomba sudah sesuai.')
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 2);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.revise', $submittedForRevision->id), [
            'verification_note' => 'Lengkapi nama penyelenggara resmi.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $revisionCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Prestasi santri berhasil diminta revisi.')
            ->assertJsonPath('data.status', 'needs_revision')
            ->assertJsonPath('data.verification_note', 'Lengkapi nama penyelenggara resmi.')
            ->assertJsonPath('data.summary.needs_action', true)
            ->assertJsonPath('data.summary.revision_count', 1);

        $submitAudit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.submitted')->firstOrFail();
        $verifyAudit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.verified')->firstOrFail();
        $revisionAudit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.revision_requested')->firstOrFail();

        self::assertSame($submitCorrelationId, $submitAudit->correlation_id);
        self::assertSame(['status', 'submitted_at', 'submitted_by'], $submitAudit->metadata['changed_fields']);
        self::assertSame('submitted', $submitAudit->metadata['result']['status']);

        self::assertSame($verifyCorrelationId, $verifyAudit->correlation_id);
        self::assertSame(['status', 'verified_at', 'verified_by', 'verification_note'], $verifyAudit->metadata['changed_fields']);
        self::assertSame('verified', $verifyAudit->metadata['result']['status']);

        self::assertSame($revisionCorrelationId, $revisionAudit->correlation_id);
        self::assertSame('Lengkapi nama penyelenggara resmi.', $revisionAudit->reason);
        self::assertSame('needs_revision', $revisionAudit->metadata['result']['status']);
    }

    public function test_needs_revision_bisa_disubmit_ulang(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = $this->achievement($category, 'needs_revision', 'PRS-LC-003');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.submit', $achievement->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.summary.revision_count', 1);
    }

    public function test_menolak_lifecycle_dengan_status_tidak_valid(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $verified = $this->achievement($category, 'verified', 'PRS-LC-004');
        $draft = $this->achievement($category, 'draft', 'PRS-LC-005');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.submit', $verified->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'PRESTASI_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.verify', $draft->id), [
            'verification_note' => 'Belum submitted.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.revise', $draft->id), [
            'verification_note' => 'Belum submitted.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_menolak_actor_tanpa_permission_lifecycle(): void
    {
        $actor = User::factory()->create();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = $this->achievement($category, 'draft', 'PRS-LC-006');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.submit', $achievement->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertForbidden();
    }

    private function operator(): User
    {
        $record = Permission::create(['name' => 'prestasi_santri.record', 'guard_name' => 'web']);
        $verify = Permission::create(['name' => 'prestasi_santri.verify', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$record, $verify, $view]);

        return $actor;
    }

    private function achievement(StudentAchievementCategoryRecord $category, string $status, string $achievementNo): StudentAchievementRecord
    {
        $studentId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-'.$achievementNo,
            'full_name' => 'Ahmad Prestasi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return StudentAchievementRecord::factory()->create([
            'achievement_no' => $achievementNo,
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $studentId,
            'student_no' => 'NIS-'.$achievementNo,
            'student_name' => 'Ahmad Prestasi',
            'title' => 'Juara Olimpiade Matematika',
            'achievement_type' => 'competition',
            'level' => 'province',
            'result' => 'Juara 1',
            'achieved_on' => '2026-09-17',
            'status' => $status,
        ]);
    }
}
