<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('men-submit draft presensi dan mengunci update langsung', function (): void {
    $actor = presensiLifecycleActor(['presensi_santri.manage', 'presensi_santri.submit']);
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'draft']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.submit', $session->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Sesi presensi santri berhasil disubmit.')
        ->assertJsonPath('data.status', 'submitted')
        ->assertJsonPath('data.submitted_by', $actor->id);

    $this->assertDatabaseHas('student_attendance_sessions', [
        'id' => $session->id,
        'status' => 'submitted',
        'submitted_by' => $actor->id,
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.update', $session->id), [
            'session_name' => 'Tidak boleh langsung',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');

    expect(AuditRecord::query()->where('module', 'PresensiSantri')->pluck('action')->all())
        ->toContain('presensi_santri.session.submitted');
});

it('membuka revisi dengan alasan lalu mengizinkan koreksi entry', function (): void {
    $actor = presensiLifecycleActor(['presensi_santri.manage', 'presensi_santri.revise']);
    $references = presensiLifecycleReferences();
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'submitted']);
    StudentAttendanceEntryRecord::factory()->create([
        'session_id' => $session->id,
        'student_id' => $references['student_id'],
        'student_no' => 'NIS-LIFE-001',
        'student_name' => 'Ahmad Lifecycle',
        'status' => 'absent',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.revise', $session->id), [
            'reason' => 'Koreksi setelah wali kelas memberi konfirmasi.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Sesi presensi santri berhasil dibuka untuk revisi.')
        ->assertJsonPath('data.status', 'revised');

    $this->assertDatabaseHas('student_attendance_revisions', [
        'session_id' => $session->id,
        'reason' => 'Koreksi setelah wali kelas memberi konfirmasi.',
        'changed_by' => $actor->id,
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.entries.update', $session->id), [
            'entries' => [
                [
                    'student_id' => $references['student_id'],
                    'status' => 'present',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('data.status', 'revised')
        ->assertJsonPath('data.summary.present', 1);

    expect(AuditRecord::query()->where('module', 'PresensiSantri')->pluck('action')->all())
        ->toContain('presensi_santri.session.revised')
        ->toContain('presensi_santri.entry.updated');
});

it('mewajibkan alasan revisi dan void', function (): void {
    $actor = presensiLifecycleActor(['presensi_santri.revise', 'presensi_santri.archive']);
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'submitted']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.revise', $session->id), [
            'reason' => 'x',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['reason']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.void', $session->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['reason']);
});

it('membatalkan sesi tanpa menghapus data dan mengunci lifecycle berikutnya', function (): void {
    $actor = presensiLifecycleActor(['presensi_santri.archive', 'presensi_santri.submit', 'presensi_santri.revise']);
    $references = presensiLifecycleReferences();
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'draft']);
    StudentAttendanceEntryRecord::factory()->create([
        'session_id' => $session->id,
        'student_id' => $references['student_id'],
        'student_no' => 'NIS-LIFE-001',
        'student_name' => 'Ahmad Lifecycle',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.void', $session->id), [
            'reason' => 'Sesi salah tanggal dan dibuat ulang.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Sesi presensi santri berhasil dibatalkan.')
        ->assertJsonPath('data.status', 'void')
        ->assertJsonPath('data.voided_by', $actor->id)
        ->assertJsonPath('data.void_reason', 'Sesi salah tanggal dan dibuat ulang.')
        ->assertJsonPath('data.summary.total', 1);

    expect(DB::table('student_attendance_entries')->where('session_id', $session->id)->count())->toBe(1);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.submit', $session->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.revise', $session->id), [
            'reason' => 'Tidak boleh revisi void.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');

    expect(AuditRecord::query()->where('module', 'PresensiSantri')->pluck('action')->all())
        ->toContain('presensi_santri.session.voided');
});

it('menolak actor tanpa permission lifecycle presensi', function (): void {
    $actor = User::factory()->create();
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'draft']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.submit', $session->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

/** @param list<string> $permissions */
function presensiLifecycleActor(array $permissions): User
{
    $view = Permission::firstOrCreate(['name' => 'presensi_santri.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();

    $actor->givePermissionTo($view);

    foreach ($permissions as $permission) {
        $actor->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    return $actor;
}

/** @return array{student_id: string} */
function presensiLifecycleReferences(): array
{
    $unitId = (string) Str::ulid();
    $studentId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'code' => 'MA-LIFE',
        'name' => 'MA Lifecycle',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => $studentId,
        'student_no' => 'NIS-LIFE-001',
        'full_name' => 'Ahmad Lifecycle',
        'primary_unit_id' => $unitId,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return ['student_id' => $studentId];
}
