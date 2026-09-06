<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('membuat sesi draft presensi beserta entry melalui API terotorisasi', function (): void {
    $actor = presensiMutationActor();
    $references = presensiMutationReferences();

    $response = $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-attendances.store'), [
            'attendance_date' => '2026-09-05',
            'context_type' => 'class_group',
            'context_id' => $references['class_group_id'],
            'context_name' => 'Kelas X A',
            'session_code' => 'kbm-pagi',
            'session_name' => 'KBM Pagi',
            'entries' => [
                [
                    'student_id' => $references['first_student_id'],
                    'status' => 'present',
                ],
                [
                    'student_id' => $references['second_student_id'],
                    'status' => 'late',
                    'minutes_late' => 15,
                    'note' => 'Terlambat apel.',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Sesi presensi santri berhasil dibuat.')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.session_code', 'KBM-PAGI')
        ->assertJsonPath('data.summary.total', 2)
        ->assertJsonPath('data.summary.present', 1)
        ->assertJsonPath('data.summary.late', 1)
        ->assertJsonPath('data.entries.0.student_no', 'NIS-MUT-001')
        ->assertJsonPath('data.entries.1.student_name', 'Budi Mutation');

    $sessionId = (string) $response->json('data.id');

    $this->assertDatabaseHas('student_attendance_sessions', [
        'id' => $sessionId,
        'status' => 'draft',
        'created_by' => $actor->id,
    ]);
    $this->assertDatabaseHas('student_attendance_entries', [
        'session_id' => $sessionId,
        'student_id' => $references['second_student_id'],
        'student_no' => 'NIS-MUT-002',
        'student_name' => 'Budi Mutation',
        'status' => 'late',
        'minutes_late' => 15,
    ]);

    expect(AuditRecord::query()->where('module', 'PresensiSantri')->pluck('action')->all())
        ->toContain('presensi_santri.session.created');
});

it('memperbarui draft sesi dan entry presensi melalui API terotorisasi', function (): void {
    $actor = presensiMutationActor();
    $references = presensiMutationReferences();
    $session = StudentAttendanceSessionRecord::factory()->create([
        'attendance_date' => '2026-09-05',
        'context_type' => 'activity',
        'context_id' => null,
        'context_name' => 'Kegiatan Umum',
        'session_code' => 'SUBUH',
        'session_name' => 'Presensi Subuh',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.update', $session->id), [
            'context_name' => 'Kegiatan Pesantren',
            'session_name' => 'Presensi Subuh Santri',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Sesi presensi santri berhasil diperbarui.')
        ->assertJsonPath('data.context_name', 'Kegiatan Pesantren')
        ->assertJsonPath('data.session_name', 'Presensi Subuh Santri');

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.entries.update', $session->id), [
            'entries' => [
                [
                    'student_id' => $references['first_student_id'],
                    'status' => 'present',
                ],
                [
                    'student_id' => $references['second_student_id'],
                    'status' => 'absent',
                    'note' => 'Tidak hadir tanpa keterangan.',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Entry presensi santri berhasil diperbarui.')
        ->assertJsonPath('data.summary.total', 2)
        ->assertJsonPath('data.summary.present', 1)
        ->assertJsonPath('data.summary.absent', 1);

    expect(AuditRecord::query()->where('module', 'PresensiSantri')->pluck('action')->all())
        ->toContain('presensi_santri.session.updated')
        ->toContain('presensi_santri.entry.updated');
});

it('menolak entry santri nonaktif dan status terlambat tanpa menit', function (): void {
    $actor = presensiMutationActor();
    $references = presensiMutationReferences();
    $session = StudentAttendanceSessionRecord::factory()->create(['status' => 'draft']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.entries.update', $session->id), [
            'entries' => [
                [
                    'student_id' => $references['inactive_student_id'],
                    'status' => 'present',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.entries.update', $session->id), [
            'entries' => [
                [
                    'student_id' => $references['first_student_id'],
                    'status' => 'late',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['entries.0.minutes_late']);
});

it('menolak update sesi yang sudah submitted atau void', function (): void {
    $actor = presensiMutationActor();
    $references = presensiMutationReferences();
    $submitted = StudentAttendanceSessionRecord::factory()->create(['status' => 'submitted']);
    $void = StudentAttendanceSessionRecord::factory()->create(['status' => 'void']);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.update', $submitted->id), [
            'session_name' => 'Nama Baru',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');

    $this->actingAs($actor)
        ->patchJson(route('api.v1.pesantrian.student-attendances.entries.update', $void->id), [
            'entries' => [
                [
                    'student_id' => $references['first_student_id'],
                    'status' => 'present',
                ],
            ],
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PRESENSI_SANTRI_MUTATION_INVALID');
});

it('menolak actor tanpa permission presensi manage untuk mutation', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.student-attendances.store'), [
            'attendance_date' => '2026-09-05',
            'context_type' => 'activity',
            'context_name' => 'Kegiatan Umum',
            'session_code' => 'SUBUH',
            'session_name' => 'Presensi Subuh',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

function presensiMutationActor(): User
{
    $view = Permission::firstOrCreate(['name' => 'presensi_santri.view', 'guard_name' => 'web']);
    $manage = Permission::firstOrCreate(['name' => 'presensi_santri.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$view, $manage]);

    return $actor;
}

/** @return array{class_group_id: string, first_student_id: string, second_student_id: string, inactive_student_id: string} */
function presensiMutationReferences(): array
{
    $unitId = (string) Str::ulid();
    $academicYearId = (string) Str::ulid();
    $academicTermId = (string) Str::ulid();
    $classLevelId = (string) Str::ulid();
    $classGroupId = (string) Str::ulid();
    $firstStudentId = (string) Str::ulid();
    $secondStudentId = (string) Str::ulid();
    $inactiveStudentId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'code' => 'MA-MUT',
        'name' => 'MA Mutation',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => $academicYearId,
        'code' => '2026-2027-MUT',
        'name' => 'Tahun Ajaran Mutation',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => $academicTermId,
        'academic_year_id' => $academicYearId,
        'code' => '2026-1-MUT',
        'name' => 'Semester Mutation',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_levels')->insert([
        'id' => $classLevelId,
        'unit_id' => $unitId,
        'code' => 'X-MUT',
        'name' => 'Kelas X Mutation',
        'sequence' => 10,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        'id' => $classGroupId,
        'academic_year_id' => $academicYearId,
        'academic_term_id' => $academicTermId,
        'unit_id' => $unitId,
        'class_level_id' => $classLevelId,
        'code' => 'X-MUT-A',
        'name' => 'Kelas X Mutation A',
        'capacity' => 30,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => $firstStudentId,
            'student_no' => 'NIS-MUT-001',
            'full_name' => 'Ahmad Mutation',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $secondStudentId,
            'student_no' => 'NIS-MUT-002',
            'full_name' => 'Budi Mutation',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $inactiveStudentId,
            'student_no' => 'NIS-MUT-003',
            'full_name' => 'Cici Nonaktif',
            'primary_unit_id' => $unitId,
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    return [
        'class_group_id' => $classGroupId,
        'first_student_id' => $firstStudentId,
        'second_student_id' => $secondStudentId,
        'inactive_student_id' => $inactiveStudentId,
    ];
}
