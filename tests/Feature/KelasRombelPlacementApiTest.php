<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupStudentRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('menempatkan santri aktif ke rombel aktif dengan audit', function (): void {
    $placement = Permission::create(['name' => 'kelas_rombel.placement', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($placement);
    $references = seedKelasRombelPlacementReferences();
    $classGroup = createKelasRombelPlacementClassGroup($references, 'VII-A');
    $studentId = seedKelasRombelPlacementStudent($references['unit_id'], 'NIS-0101', 'Aisyah Placement');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.students.store', $classGroup->id), [
        'student_id' => $studentId,
        'joined_on' => '2026-07-15',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Santri berhasil ditempatkan ke rombel.')
        ->assertJsonPath('data.class_group_id', $classGroup->id)
        ->assertJsonPath('data.student_id', $studentId)
        ->assertJsonPath('data.student_no', 'NIS-0101')
        ->assertJsonPath('data.status', 'active');

    self::assertDatabaseHas('class_group_students', [
        'class_group_id' => $classGroup->id,
        'academic_term_id' => $references['term_id'],
        'student_id' => $studentId,
        'student_no' => 'NIS-0101',
        'status' => 'active',
        'active_period_student_key' => $references['term_id'].':'.$studentId,
    ]);

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.student.placed');
});

it('memindahkan santri dari rombel lama ke rombel baru dengan alasan dan audit', function (): void {
    $placement = Permission::create(['name' => 'kelas_rombel.placement', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($placement);
    $references = seedKelasRombelPlacementReferences();
    $oldClassGroup = createKelasRombelPlacementClassGroup($references, 'VII-A');
    $newClassGroup = createKelasRombelPlacementClassGroup($references, 'VII-B');
    $studentId = seedKelasRombelPlacementStudent($references['unit_id'], 'NIS-0102', 'Budi Transfer');
    $oldPlacement = ClassGroupStudentRecord::query()->create([
        'class_group_id' => $oldClassGroup->id,
        'academic_term_id' => $references['term_id'],
        'student_id' => $studentId,
        'student_no' => 'NIS-0102',
        'joined_on' => '2026-07-15',
        'status' => 'active',
        'active_period_student_key' => $references['term_id'].':'.$studentId,
    ]);

    $response = $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.students.transfer', [$oldClassGroup->id, $oldPlacement->id]), [
        'target_class_group_id' => $newClassGroup->id,
        'joined_on' => '2026-08-01',
        'reason' => 'Pindah rombel sesuai asesmen akademik.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Santri berhasil dipindahkan ke rombel baru.')
        ->assertJsonPath('data.previous.id', $oldPlacement->id)
        ->assertJsonPath('data.previous.status', 'transferred')
        ->assertJsonPath('data.current.class_group_id', $newClassGroup->id)
        ->assertJsonPath('data.current.status', 'active');

    $newPlacementId = (string) $response->json('data.current.id');

    self::assertDatabaseHas('class_group_students', [
        'id' => $oldPlacement->id,
        'status' => 'transferred',
        'left_on' => '2026-07-31 00:00:00',
        'active_period_student_key' => null,
    ]);
    self::assertDatabaseHas('class_group_students', [
        'id' => $newPlacementId,
        'class_group_id' => $newClassGroup->id,
        'status' => 'active',
        'active_period_student_key' => $references['term_id'].':'.$studentId,
    ]);

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.student.transferred');
});

it('mengeluarkan santri dari rombel aktif dengan alasan dan audit', function (): void {
    $placement = Permission::create(['name' => 'kelas_rombel.placement', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($placement);
    $references = seedKelasRombelPlacementReferences();
    $classGroup = createKelasRombelPlacementClassGroup($references, 'VII-A');
    $studentId = seedKelasRombelPlacementStudent($references['unit_id'], 'NIS-0103', 'Citra Remove');
    $activePlacement = ClassGroupStudentRecord::query()->create([
        'class_group_id' => $classGroup->id,
        'academic_term_id' => $references['term_id'],
        'student_id' => $studentId,
        'student_no' => 'NIS-0103',
        'joined_on' => '2026-07-15',
        'status' => 'active',
        'active_period_student_key' => $references['term_id'].':'.$studentId,
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.students.remove', [$classGroup->id, $activePlacement->id]), [
        'left_on' => '2026-08-15',
        'reason' => 'Keluar dari rombel karena pindah unit.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Santri berhasil dikeluarkan dari rombel.')
        ->assertJsonPath('data.id', $activePlacement->id)
        ->assertJsonPath('data.status', 'removed')
        ->assertJsonPath('data.reason', 'Keluar dari rombel karena pindah unit.');

    self::assertDatabaseHas('class_group_students', [
        'id' => $activePlacement->id,
        'status' => 'removed',
        'left_on' => '2026-08-15 00:00:00',
        'active_period_student_key' => null,
    ]);

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.student.removed');
});

it('menolak placement untuk santri tidak aktif rombel tidak aktif dan actor tanpa permission', function (): void {
    $actor = User::factory()->create();
    $references = seedKelasRombelPlacementReferences();
    $inactiveClassGroup = createKelasRombelPlacementClassGroup($references, 'VII-A', status: 'closed');
    $inactiveStudentId = seedKelasRombelPlacementStudent($references['unit_id'], 'NIS-0104', 'Dedi Nonaktif', status: 'graduated');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.students.store', $inactiveClassGroup->id), [
        'student_id' => $inactiveStudentId,
        'joined_on' => '2026-07-15',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();

    $permission = Permission::create(['name' => 'kelas_rombel.placement', 'guard_name' => 'web']);
    $actor->givePermissionTo($permission);

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.students.store', $inactiveClassGroup->id), [
        'student_id' => $inactiveStudentId,
        'joined_on' => '2026-07-15',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'KELAS_ROMBEL_PLACEMENT_INVALID');
});

/** @return array{unit_id: string, year_id: string, term_id: string} */
function seedKelasRombelPlacementReferences(): array
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T35AB1',
        'code' => 'MTS-PLC',
        'name' => 'MTs Placement',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T35AC1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T35AC2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T35AC1',
        'code' => '2026-1',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'unit_id' => '01K41KRG60H6GTYB56B6T35AB1',
        'year_id' => '01K41KRG60H6GTYB56B6T35AC1',
        'term_id' => '01K41KRG60H6GTYB56B6T35AC2',
    ];
}

/** @param array{unit_id: string, year_id: string, term_id: string} $references */
function createKelasRombelPlacementClassGroup(array $references, string $code, string $status = 'active'): ClassGroupRecord
{
    $curriculum = AcademicCurriculumRecord::query()->firstOrCreate(
        ['code' => 'KUR-PLC'],
        ['name' => 'Kurikulum Placement', 'status' => 'active'],
    );
    $level = ClassLevelRecord::query()->firstOrCreate(
        ['unit_id' => $references['unit_id'], 'code' => 'VII'],
        ['name' => 'Kelas VII', 'sequence' => 7, 'status' => 'active'],
    );

    /** @var ClassGroupRecord $classGroup */
    $classGroup = ClassGroupRecord::query()->create([
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => $code,
        'name' => $code,
        'capacity' => 32,
        'status' => $status,
    ]);

    return $classGroup;
}

function seedKelasRombelPlacementStudent(string $unitId, string $studentNo, string $fullName, string $status = 'active'): string
{
    $id = (string) Str::ulid();

    DB::table('students')->insert([
        'id' => $id,
        'student_no' => $studentNo,
        'full_name' => $fullName,
        'primary_unit_id' => $unitId,
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}
