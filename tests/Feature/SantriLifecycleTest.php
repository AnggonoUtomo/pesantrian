<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SantriLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengubah_status_lifecycle_santri_dengan_alasan_wajib_dan_audit(): void
    {
        $permission = Permission::create(['name' => 'santri.lifecycle', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($permission);
        $correlationId = (string) Str::ulid();

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-0101',
            'full_name' => 'Aisyah Lifecycle',
            'status' => 'active',
            'status_reason' => null,
            'status_changed_at' => null,
            'status_changed_by' => null,
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.lifecycle', $student->id),
            [
                'status' => 'inactive',
                'reason' => 'Cuti sementara atas permintaan wali.',
            ],
            [
                'Idempotency-Key' => (string) Str::ulid(),
                'X-Correlation-ID' => $correlationId,
            ],
        )
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Status santri berhasil diperbarui.')
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.status_reason', 'Cuti sementara atas permintaan wali.')
            ->assertJsonPath('data.status_changed_by', $actor->id);

        $student->refresh();

        self::assertSame('inactive', $student->status);
        self::assertSame('Cuti sementara atas permintaan wali.', $student->status_reason);
        self::assertSame($actor->id, $student->status_changed_by);
        self::assertNotNull($student->status_changed_at);

        $audit = AuditRecord::query()
            ->where('action', 'santri.student.lifecycle_changed')
            ->firstOrFail();

        self::assertSame('Santri', $audit->module);
        self::assertSame($actor->id, $audit->actor_id);
        self::assertSame($student->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('active', $audit->metadata['from_status']);
        self::assertSame('inactive', $audit->metadata['to_status']);
        self::assertSame('NIS-0101', $audit->metadata['result']['student_no']);
        self::assertSame('inactive', $audit->metadata['result']['status']);
        self::assertSame('Cuti sementara atas permintaan wali.', $audit->reason);
    }

    public function test_menolak_status_lifecycle_invalid_dan_alasan_kosong_untuk_status_terminal(): void
    {
        $permission = Permission::create(['name' => 'santri.lifecycle', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($permission);
        $student = StudentRecord::factory()->create(['status' => 'active']);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.lifecycle', $student->id),
            ['status' => 'alumni'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['status']]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.lifecycle', $student->id),
            ['status' => 'graduated'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['reason']]);
    }

    public function test_archive_restore_santri_mengubah_visibility_default_dan_mencatat_audit(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $archive = Permission::create(['name' => 'santri.archive', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$view, $archive]);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-0201',
            'full_name' => 'Santri Arsip',
            'status' => 'active',
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.archive', $student->id),
            ['reason' => 'Data duplikat dari migrasi awal.'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data santri berhasil diarsipkan.')
            ->assertJsonPath('data.id', $student->id)
            ->assertJsonPath('data.archived_by', $actor->id);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.index'))
            ->assertOk()
            ->assertJsonPath('meta.total', 0);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.show', $student->id))
            ->assertNotFound();

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.restore', $student->id),
            ['reason' => 'Data dipakai kembali.'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data santri berhasil dipulihkan.')
            ->assertJsonPath('data.id', $student->id)
            ->assertJsonPath('data.archived_at', null)
            ->assertJsonPath('data.archived_by', null);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.index'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        self::assertSame(1, AuditRecord::query()->where('action', 'santri.student.archived')->count());
        self::assertSame(1, AuditRecord::query()->where('action', 'santri.student.restored')->count());
    }

    public function test_menolak_actor_tanpa_permission_dan_resource_lifecycle_archive_tidak_ditemukan(): void
    {
        $actor = User::factory()->create();
        $student = StudentRecord::factory()->create();

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.lifecycle', $student->id),
            ['status' => 'inactive', 'reason' => 'Tidak berizin.'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');

        $archive = Permission::create(['name' => 'santri.archive', 'guard_name' => 'web']);
        $archiver = User::factory()->create();
        $archiver->givePermissionTo($archive);

        $this->actingAs($archiver)->patchJson(
            route('api.v1.pesantrian.students.archive', (string) Str::ulid()),
            ['reason' => 'Missing.'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

        $this->actingAs($archiver)->patchJson(
            route('api.v1.pesantrian.students.restore', (string) Str::ulid()),
            ['reason' => 'Missing.'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }
}
