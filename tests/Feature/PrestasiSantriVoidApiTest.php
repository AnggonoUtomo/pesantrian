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

final class PrestasiSantriVoidApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membatalkan_prestasi_dengan_alasan_tanpa_menghapus_data_dan_mencatat_audit(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create(['code' => 'LMB', 'name' => 'Lomba']);
        $achievement = $this->achievement($category, 'verified', 'PRS-VOID-001');
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.void', $achievement->id), [
            'void_reason' => 'Sertifikat terkonfirmasi salah input dan diganti catatan baru.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Prestasi santri berhasil dibatalkan.')
            ->assertJsonPath('data.status', 'void')
            ->assertJsonPath('data.voided_by', $actor->id)
            ->assertJsonPath('data.void_reason', 'Sertifikat terkonfirmasi salah input dan diganti catatan baru.')
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 1);

        self::assertSame(1, StudentAchievementRecord::query()->whereKey($achievement->id)->count());

        $this->actingAs($actor)->getJson(route('api.v1.pesantrian.prestasi-santri.show', $achievement->id))
            ->assertOk()
            ->assertJsonPath('data.status', 'void')
            ->assertJsonPath('data.void_reason', 'Sertifikat terkonfirmasi salah input dan diganti catatan baru.')
            ->assertJsonPath('data.revisions.0.to_status', 'void')
            ->assertJsonPath('data.revisions.0.reason', 'Sertifikat terkonfirmasi salah input dan diganti catatan baru.');

        $audit = AuditRecord::query()->where('action', 'prestasi_santri.achievement.voided')->firstOrFail();

        self::assertSame('PrestasiSantri', $audit->module);
        self::assertSame('student_achievement', $audit->subject_type);
        self::assertSame($achievement->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('Sertifikat terkonfirmasi salah input dan diganti catatan baru.', $audit->reason);
        self::assertSame(['status', 'voided_at', 'voided_by', 'void_reason'], $audit->metadata['changed_fields']);
        self::assertSame('void', $audit->metadata['result']['status']);
        self::assertArrayNotHasKey('void_reason', $audit->metadata['result']);
    }

    public function test_mewajibkan_alasan_void(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = $this->achievement($category, 'submitted', 'PRS-VOID-002');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.void', $achievement->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['void_reason']);
    }

    public function test_menolak_void_ulang_untuk_prestasi_yang_sudah_void(): void
    {
        $actor = $this->operator();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = $this->achievement($category, 'void', 'PRS-VOID-003');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.void', $achievement->id), [
            'void_reason' => 'Percobaan pembatalan ulang.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'PRESTASI_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_menolak_actor_tanpa_permission_archive_untuk_void(): void
    {
        Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);

        $actor = User::factory()->create();
        $category = StudentAchievementCategoryRecord::factory()->create();
        $achievement = $this->achievement($category, 'verified', 'PRS-VOID-004');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.void', $achievement->id), [
            'void_reason' => 'Pembatalan tanpa permission sensitif.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertForbidden();
    }

    private function operator(): User
    {
        $archive = Permission::create(['name' => 'prestasi_santri.archive', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$archive, $view]);

        return $actor;
    }

    private function achievement(StudentAchievementCategoryRecord $category, string $status, string $achievementNo): StudentAchievementRecord
    {
        $studentId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-'.$achievementNo,
            'full_name' => 'Fatimah Prestasi',
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
            'student_name' => 'Fatimah Prestasi',
            'title' => 'Juara Musabaqah',
            'achievement_type' => 'competition',
            'level' => 'regency',
            'result' => 'Juara 2',
            'achieved_on' => '2026-09-18',
            'status' => $status,
        ]);
    }
}
