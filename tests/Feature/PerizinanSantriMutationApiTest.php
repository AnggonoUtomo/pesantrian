<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('membuat permohonan izin draft dengan snapshot santri dan wali utama', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();

    $response = $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-permits.store'), [
            'student_id' => $references['first_student_id'],
            'permit_type' => 'home_visit',
            'starts_at' => '2026-09-15 08:00:00',
            'ends_at' => '2026-09-15 17:00:00',
            'destination' => 'Rumah wali',
            'reason' => 'Pulang bersama wali untuk keperluan keluarga.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Permohonan izin santri berhasil dibuat.')
        ->assertJsonPath('data.permit_no', 'IZN-000001')
        ->assertJsonPath('data.student_id', $references['first_student_id'])
        ->assertJsonPath('data.student_no', 'NIS-IZN-MUT-001')
        ->assertJsonPath('data.student_name', 'Ahmad Izin Mutation')
        ->assertJsonPath('data.guardian_name', 'Siti Mutation')
        ->assertJsonPath('data.guardian_phone', '081234567890')
        ->assertJsonPath('data.guardian_relation', 'ibu')
        ->assertJsonPath('data.status', 'draft');

    $permitId = (string) $response->json('data.id');

    $this->assertDatabaseHas('student_permits', [
        'id' => $permitId,
        'permit_no' => 'IZN-000001',
        'student_id' => $references['first_student_id'],
        'status' => 'draft',
        'created_by' => $actor->id,
    ]);

    expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
        ->toContain('perizinan_santri.permit.created');
});

it('memperbarui permohonan izin draft dan mencatat revision', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();
    $permit = StudentPermitRecord::factory()->create([
        'student_id' => $references['first_student_id'],
        'student_no' => 'NIS-IZN-MUT-001',
        'student_name' => 'Ahmad Izin Mutation',
        'permit_type' => 'leave',
        'starts_at' => '2026-09-15 08:00:00',
        'ends_at' => '2026-09-15 12:00:00',
        'destination' => 'Kota lama',
        'reason' => 'Keperluan awal.',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-permits.update', $permit->id), [
            'permit_type' => 'activity',
            'ends_at' => '2026-09-15 18:00:00',
            'destination' => 'Gedung kegiatan',
            'reason' => 'Mengikuti kegiatan keluarga.',
            'revision_reason' => 'Koreksi jenis izin dan jam kembali.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Permohonan izin santri berhasil diperbarui.')
        ->assertJsonPath('data.permit_type', 'activity')
        ->assertJsonPath('data.destination', 'Gedung kegiatan')
        ->assertJsonPath('data.summary.revision_count', 1)
        ->assertJsonPath('data.revisions.0.reason', 'Koreksi jenis izin dan jam kembali.');

    $this->assertDatabaseHas('student_permit_revisions', [
        'permit_id' => $permit->id,
        'reason' => 'Koreksi jenis izin dan jam kembali.',
        'changed_by' => $actor->id,
    ]);

    expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
        ->toContain('perizinan_santri.permit.updated');
});

it('men-submit draft bila tidak overlap dengan izin aktif santri', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();
    $permit = StudentPermitRecord::factory()->create([
        'student_id' => $references['first_student_id'],
        'starts_at' => '2026-09-15 08:00:00',
        'ends_at' => '2026-09-15 17:00:00',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-permits.submit', $permit->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Permohonan izin santri berhasil disubmit.')
        ->assertJsonPath('data.status', 'submitted')
        ->assertJsonPath('data.submitted_by', $actor->id)
        ->assertJsonPath('data.summary.is_active', true)
        ->assertJsonPath('data.summary.revision_count', 1);

    $this->assertDatabaseHas('student_permits', [
        'id' => $permit->id,
        'status' => 'submitted',
        'submitted_by' => $actor->id,
    ]);

    expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
        ->toContain('perizinan_santri.permit.submitted');
});

it('menolak santri nonaktif dan rentang waktu tidak valid', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-permits.store'), [
            'student_id' => $references['inactive_student_id'],
            'permit_type' => 'leave',
            'starts_at' => '2026-09-15 08:00:00',
            'ends_at' => '2026-09-15 17:00:00',
            'reason' => 'Keperluan keluarga.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-permits.store'), [
            'student_id' => $references['first_student_id'],
            'permit_type' => 'leave',
            'starts_at' => '2026-09-15 18:00:00',
            'ends_at' => '2026-09-15 08:00:00',
            'reason' => 'Keperluan keluarga.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ends_at']);
});

it('menolak update non-draft dan submit yang overlap dengan izin aktif', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();
    $submitted = StudentPermitRecord::factory()->create([
        'student_id' => $references['first_student_id'],
        'starts_at' => '2026-09-14 08:00:00',
        'ends_at' => '2026-09-14 17:00:00',
        'status' => 'submitted',
    ]);
    $overlap = StudentPermitRecord::factory()->create([
        'student_id' => $references['first_student_id'],
        'starts_at' => '2026-09-15 08:00:00',
        'ends_at' => '2026-09-15 17:00:00',
        'status' => 'approved',
    ]);
    $draft = StudentPermitRecord::factory()->create([
        'student_id' => $references['first_student_id'],
        'starts_at' => '2026-09-15 12:00:00',
        'ends_at' => '2026-09-15 18:00:00',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-permits.update', $submitted->id), [
            'reason' => 'Tidak boleh diedit.',
            'revision_reason' => 'Mencoba update non-draft.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-permits.submit', $draft->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

    $this->assertDatabaseHas('student_permits', [
        'id' => $draft->id,
        'status' => 'draft',
    ]);
    $this->assertDatabaseHas('student_permits', [
        'id' => $overlap->id,
        'status' => 'approved',
    ]);
});

it('menolak submit bila santri draft sudah tidak aktif', function (): void {
    $actor = perizinanMutationActor(['perizinan_santri.manage']);
    $references = perizinanMutationReferences();
    $permit = StudentPermitRecord::factory()->create([
        'student_id' => $references['inactive_student_id'],
        'starts_at' => '2026-09-16 08:00:00',
        'ends_at' => '2026-09-16 17:00:00',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-permits.submit', $permit->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

    $this->assertDatabaseHas('student_permits', [
        'id' => $permit->id,
        'status' => 'draft',
    ]);
});

it('menolak actor tanpa permission manage untuk mutation perizinan', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-permits.store'), [
            'student_id' => (string) Str::ulid(),
            'permit_type' => 'leave',
            'starts_at' => '2026-09-15 08:00:00',
            'ends_at' => '2026-09-15 17:00:00',
            'reason' => 'Keperluan keluarga.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

/** @param list<string> $permissions */
function perizinanMutationActor(array $permissions): User
{
    $view = Permission::firstOrCreate(['name' => 'perizinan_santri.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();

    $actor->givePermissionTo($view);

    foreach ($permissions as $permission) {
        $actor->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    return $actor;
}

/** @return array{first_student_id: string, second_student_id: string, inactive_student_id: string} */
function perizinanMutationReferences(): array
{
    $unitId = (string) Str::ulid();
    $firstStudentId = (string) Str::ulid();
    $secondStudentId = (string) Str::ulid();
    $inactiveStudentId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'code' => 'MA-IZN-MUT',
        'name' => 'MA Izin Mutation',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => $firstStudentId,
            'student_no' => 'NIS-IZN-MUT-001',
            'full_name' => 'Ahmad Izin Mutation',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $secondStudentId,
            'student_no' => 'NIS-IZN-MUT-002',
            'full_name' => 'Budi Izin Mutation',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $inactiveStudentId,
            'student_no' => 'NIS-IZN-MUT-003',
            'full_name' => 'Cici Izin Nonaktif',
            'primary_unit_id' => $unitId,
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_guardians')->insert([
        'id' => (string) Str::ulid(),
        'student_id' => $firstStudentId,
        'guardian_name' => 'Siti Mutation',
        'guardian_phone' => '081234567890',
        'guardian_relation' => 'ibu',
        'is_primary' => true,
        'is_emergency_contact' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'first_student_id' => $firstStudentId,
        'second_student_id' => $secondStudentId,
        'inactive_student_id' => $inactiveStudentId,
    ];
}
