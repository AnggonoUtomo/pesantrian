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

final class KedisiplinanSantriCategoryMutationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_memperbarui_dan_mengarsipkan_kategori_dengan_audit(): void
    {
        $actor = $this->manager();
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();
        $archiveCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-categories.store'), [
            'code' => 'TERLAMBAT_SUBUH',
            'name' => 'Terlambat Subuh',
            'description' => 'Santri terlambat mengikuti kegiatan subuh.',
            'default_severity' => 'minor',
            'default_points' => 5,
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Kategori kedisiplinan santri berhasil dibuat.')
            ->assertJsonPath('data.code', 'TERLAMBAT_SUBUH')
            ->assertJsonPath('data.status', 'active');

        $categoryId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-categories.update', $categoryId), [
            'name' => 'Terlambat Shalat Subuh',
            'default_severity' => 'moderate',
            'default_points' => 10,
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Kategori kedisiplinan santri berhasil diperbarui.')
            ->assertJsonPath('data.name', 'Terlambat Shalat Subuh')
            ->assertJsonPath('data.default_severity', 'moderate')
            ->assertJsonPath('data.default_points', 10)
            ->assertJsonPath('data.status', 'active');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-categories.archive', $categoryId), [
            'reason' => 'Digabung ke kategori tata tertib.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $archiveCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Kategori kedisiplinan santri berhasil diarsipkan.')
            ->assertJsonPath('data.status', 'archived');

        $createAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.category.created')->firstOrFail();
        $updateAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.category.updated')->firstOrFail();
        $archiveAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.category.archived')->firstOrFail();

        self::assertSame('KedisiplinanSantri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student_discipline_category', $createAudit->subject_type);
        self::assertSame($categoryId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertSame('TERLAMBAT_SUBUH', $createAudit->metadata['result']['code']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame(['name', 'default_severity', 'default_points'], $updateAudit->metadata['changed_fields']);
        self::assertSame('Terlambat Shalat Subuh', $updateAudit->metadata['result']['name']);

        self::assertSame($archiveCorrelationId, $archiveAudit->correlation_id);
        self::assertSame(['status'], $archiveAudit->metadata['changed_fields']);
        self::assertSame('Digabung ke kategori tata tertib.', $archiveAudit->reason);
    }

    public function test_archive_kategori_tidak_menghapus_histori_kasus_dan_tidak_muncul_di_list_active(): void
    {
        $actor = $this->manager();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ASRAMA',
            'name' => 'Pelanggaran Asrama',
            'status' => 'active',
        ]);
        $case = StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-CAT-001',
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-CAT-001',
            'student_name' => 'Ahmad Kategori',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'resolved',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-categories.archive', $category->id), [
            'reason' => 'Kategori lama tidak dipakai untuk kasus baru.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');

        $this->assertDatabaseHas('student_discipline_cases', [
            'id' => $case->id,
            'category_id' => $category->id,
            'category_name' => 'Pelanggaran Asrama',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-categories.index').'?'.http_build_query(['status' => 'active']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_menolak_kode_kategori_duplikat(): void
    {
        $actor = $this->manager();

        StudentDisciplineCategoryRecord::factory()->create(['code' => 'DUPLIKAT']);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-categories.store'), [
            'code' => 'DUPLIKAT',
            'name' => 'Duplikat Kategori',
            'default_severity' => 'minor',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_menolak_actor_tanpa_permission_manage_atau_archive(): void
    {
        $actor = User::factory()->create();
        $category = StudentDisciplineCategoryRecord::factory()->create();

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.student-discipline-categories.store'), [
            'code' => 'NOAUTH',
            'name' => 'Tanpa Akses',
            'default_severity' => 'minor',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $manageOnly = $this->manager(withArchive: false);

        $this->actingAs($manageOnly)->patchJson(route('api.v1.pesantrian.student-discipline-categories.archive', $category->id), [
            'reason' => 'Coba arsip tanpa permission.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function manager(bool $withArchive = true): User
    {
        $manage = Permission::create(['name' => 'kedisiplinan_santri.manage', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'kedisiplinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$manage, $view]);

        if ($withArchive) {
            $archive = Permission::create(['name' => 'kedisiplinan_santri.archive', 'guard_name' => 'web']);
            $actor->givePermissionTo($archive);
        }

        return $actor;
    }

    /** @return array{student_id: string} */
    private function seedReferences(): array
    {
        $studentId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-CAT-001',
            'full_name' => 'Ahmad Kategori',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['student_id' => $studentId];
    }
}
