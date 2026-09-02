<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('menetapkan dan mengakhiri wali kelas aktif dengan audit', function (): void {
    $manage = Permission::create(['name' => 'kelas_rombel.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $references = seedKelasRombelHomeroomReferences();
    $classGroup = createKelasRombelHomeroomClassGroup($references, 'VII-A');
    $employeeId = seedKelasRombelHomeroomEmployee($references['unit_id'], 'PEG-0101', 'Ustadz Hasan');

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.homerooms.store', $classGroup->id), [
        'employee_id' => $employeeId,
        'assigned_on' => '2026-07-01',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Wali kelas berhasil ditetapkan.')
        ->assertJsonPath('data.class_group_id', $classGroup->id)
        ->assertJsonPath('data.employee_id', $employeeId)
        ->assertJsonPath('data.employee_name', 'Ustadz Hasan')
        ->assertJsonPath('data.status', 'active');

    $homeroomId = (string) $created->json('data.id');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.homerooms.store', $classGroup->id), [
        'employee_id' => $employeeId,
        'assigned_on' => '2026-07-02',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'KELAS_ROMBEL_HOMEROOM_INVALID');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.homerooms.end', [$classGroup->id, $homeroomId]), [
        'ended_on' => '2026-12-31',
        'reason' => 'Pergantian wali kelas semester berikutnya.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Wali kelas berhasil diakhiri.')
        ->assertJsonPath('data.id', $homeroomId)
        ->assertJsonPath('data.status', 'ended')
        ->assertJsonPath('data.reason', 'Pergantian wali kelas semester berikutnya.');

    self::assertDatabaseHas('class_group_homerooms', [
        'id' => $homeroomId,
        'status' => 'ended',
        'ended_on' => '2026-12-31 00:00:00',
        'active_class_group_key' => null,
    ]);

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.homeroom.assigned')
        ->toContain('kelas_rombel.homeroom.ended');
});

it('menolak wali kelas dari pegawai tidak aktif atau beda unit dan actor tanpa manage', function (): void {
    $actor = User::factory()->create();
    $references = seedKelasRombelHomeroomReferences();
    $classGroup = createKelasRombelHomeroomClassGroup($references, 'VII-A');
    $inactiveEmployeeId = seedKelasRombelHomeroomEmployee($references['unit_id'], 'PEG-0102', 'Ustadz Nonaktif', status: 'inactive');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.homerooms.store', $classGroup->id), [
        'employee_id' => $inactiveEmployeeId,
        'assigned_on' => '2026-07-01',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();

    $manage = Permission::create(['name' => 'kelas_rombel.manage', 'guard_name' => 'web']);
    $actor->givePermissionTo($manage);

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.homerooms.store', $classGroup->id), [
        'employee_id' => $inactiveEmployeeId,
        'assigned_on' => '2026-07-01',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'KELAS_ROMBEL_HOMEROOM_INVALID');
});

it('mengarsipkan dan memulihkan rombel dengan audit dan list default tetap aktif saja', function (): void {
    $archive = Permission::create(['name' => 'kelas_rombel.archive', 'guard_name' => 'web']);
    $view = Permission::create(['name' => 'kelas_rombel.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$archive, $view]);
    $references = seedKelasRombelHomeroomReferences();
    $classGroup = createKelasRombelHomeroomClassGroup($references, 'VII-A');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.archive', $classGroup->id), [
        'reason' => 'Rombel tidak dipakai pada semester ini.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Rombel berhasil diarsipkan.')
        ->assertJsonPath('data.id', $classGroup->id)
        ->assertJsonPath('data.status', 'archived');

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.class-groups.index'))
        ->assertOk()
        ->assertJsonPath('meta.total', 0);

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.restore', $classGroup->id), [], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Rombel berhasil dipulihkan.')
        ->assertJsonPath('data.id', $classGroup->id)
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.archived_at', null);

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.class_group.archived')
        ->toContain('kelas_rombel.class_group.restored');
});

it('menolak archive rombel untuk actor tanpa permission archive', function (): void {
    $actor = User::factory()->create();
    $references = seedKelasRombelHomeroomReferences();
    $classGroup = createKelasRombelHomeroomClassGroup($references, 'VII-A');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.archive', $classGroup->id), [
        'reason' => 'Tidak boleh.',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

/** @return array{unit_id: string, year_id: string, term_id: string} */
function seedKelasRombelHomeroomReferences(): array
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T36AB1',
        'code' => 'MTS-HMR',
        'name' => 'MTs Homeroom',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T36AC1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T36AC2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T36AC1',
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
        'unit_id' => '01K41KRG60H6GTYB56B6T36AB1',
        'year_id' => '01K41KRG60H6GTYB56B6T36AC1',
        'term_id' => '01K41KRG60H6GTYB56B6T36AC2',
    ];
}

/** @param array{unit_id: string, year_id: string, term_id: string} $references */
function createKelasRombelHomeroomClassGroup(array $references, string $code, string $status = 'active'): ClassGroupRecord
{
    $curriculum = AcademicCurriculumRecord::query()->firstOrCreate(
        ['code' => 'KUR-HMR'],
        ['name' => 'Kurikulum Homeroom', 'status' => 'active'],
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

function seedKelasRombelHomeroomEmployee(string $unitId, string $employeeNo, string $name, string $status = 'active'): string
{
    $id = (string) Str::ulid();

    DB::table('employees')->insert([
        'id' => $id,
        'primary_unit_id' => $unitId,
        'employee_no' => $employeeNo,
        'name' => $name,
        'employment_type' => 'teacher',
        'position' => 'Wali Kelas',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}
