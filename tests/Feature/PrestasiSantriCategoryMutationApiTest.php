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

final class PrestasiSantriCategoryMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_memperbarui_dan_mengarsipkan_kategori_dengan_audit(): void
    {
        $actor = $this->manager();
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();
        $archiveCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.categories.store'), [
            'code' => 'AKD',
            'name' => 'Akademik',
            'description' => 'Prestasi lomba akademik santri.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Kategori prestasi santri berhasil dibuat.')
            ->assertJsonPath('data.code', 'AKD')
            ->assertJsonPath('data.status', 'active');

        $categoryId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.categories.update', $categoryId), [
            'name' => 'Prestasi Akademik',
            'description' => 'Kategori prestasi akademik formal dan lomba.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Kategori prestasi santri berhasil diperbarui.')
            ->assertJsonPath('data.name', 'Prestasi Akademik')
            ->assertJsonPath('data.description', 'Kategori prestasi akademik formal dan lomba.')
            ->assertJsonPath('data.status', 'active');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.categories.archive', $categoryId), [
            'reason' => 'Digabung ke kategori lomba akademik.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $archiveCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Kategori prestasi santri berhasil diarsipkan.')
            ->assertJsonPath('data.status', 'archived');

        $createAudit = AuditRecord::query()->where('action', 'prestasi_santri.category.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'prestasi_santri.category.updated')->firstOrFail();
        $archiveAudit = AuditRecord::query()->where('action', 'prestasi_santri.category.archived')->firstOrFail();

        self::assertSame('PrestasiSantri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student_achievement_category', $createAudit->subject_type);
        self::assertSame($categoryId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('AKD', $createAudit->metadata['result']['code']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame(['name', 'description'], $updateAudit->metadata['changed_fields']);
        self::assertSame('Prestasi Akademik', $updateAudit->metadata['result']['name']);

        self::assertSame($archiveCorrelationId, $archiveAudit->correlation_id);
        self::assertSame(['status'], $archiveAudit->metadata['changed_fields']);
        self::assertSame('Digabung ke kategori lomba akademik.', $archiveAudit->reason);
    }

    public function test_archive_kategori_tidak_menghapus_histori_prestasi_dan_tidak_muncul_di_list_active(): void
    {
        $actor = $this->manager();
        $studentId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-PRS-CAT',
            'full_name' => 'Ahmad Prestasi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = StudentAchievementCategoryRecord::factory()->create([
            'code' => 'ARS',
            'name' => 'Prestasi Arsip',
            'status' => 'active',
        ]);
        $achievement = StudentAchievementRecord::factory()->create([
            'achievement_no' => 'PRS-CAT-001',
            'student_id' => $studentId,
            'student_no' => 'NIS-PRS-CAT',
            'student_name' => 'Ahmad Prestasi',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'verified',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.prestasi-santri.categories.archive', $category->id), [
            'reason' => 'Kategori lama tidak dipakai untuk prestasi baru.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');

        $this->assertDatabaseHas('student_achievements', [
            'id' => $achievement->id,
            'category_id' => $category->id,
            'category_name' => 'Prestasi Arsip',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.prestasi-santri.categories.index').'?'.http_build_query(['status' => 'active']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_menolak_kode_kategori_duplikat(): void
    {
        $actor = $this->manager();

        StudentAchievementCategoryRecord::factory()->create(['code' => 'DUPLIKAT']);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.categories.store'), [
            'code' => 'DUPLIKAT',
            'name' => 'Duplikat Kategori',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_menolak_actor_tanpa_permission_manage_atau_archive(): void
    {
        $actor = User::factory()->create();
        $category = StudentAchievementCategoryRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.prestasi-santri.categories.store'), [
            'code' => 'NOAUTH',
            'name' => 'Tanpa Akses',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $manageOnly = $this->manager(withArchive: false);

        $this->actingAs($manageOnly)->patchJson(route('api.v1.pesantrian.prestasi-santri.categories.archive', $category->id), [
            'reason' => 'Coba arsip tanpa permission.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function manager(bool $withArchive = true): User
    {
        $manage = Permission::create(['name' => 'prestasi_santri.manage', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'prestasi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$manage, $view]);

        if ($withArchive) {
            $archive = Permission::create(['name' => 'prestasi_santri.archive', 'guard_name' => 'web']);
            $actor->givePermissionTo($archive);
        }

        return $actor;
    }
}
