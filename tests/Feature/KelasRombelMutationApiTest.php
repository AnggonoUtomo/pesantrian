<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('membuat dan memperbarui kurikulum melalui API terotorisasi dengan audit', function (): void {
    $manage = Permission::create(['name' => 'kelas_rombel.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.curricula.store'), [
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'description' => 'Kurikulum operasional awal.',
        'status' => 'draft',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Kurikulum berhasil dibuat.')
        ->assertJsonPath('data.code', 'KUR-2026')
        ->assertJsonPath('data.status', 'draft');

    $curriculumId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.curricula.update', $curriculumId), [
        'name' => 'Kurikulum Merdeka Pesantren',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Kurikulum berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Kurikulum Merdeka Pesantren')
        ->assertJsonPath('data.status', 'active');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.curricula.store'), [
        'code' => 'KUR-2026',
        'name' => 'Duplikat',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable();

    expect(AuditRecord::query()->where('module', 'KelasRombel')->pluck('action')->all())
        ->toContain('kelas_rombel.curriculum.created')
        ->toContain('kelas_rombel.curriculum.updated');
});

it('membuat dan memperbarui tingkat kelas melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'kelas_rombel.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $references = seedKelasRombelMutationReferences();

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.levels.store'), [
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'draft',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Tingkat kelas berhasil dibuat.')
        ->assertJsonPath('data.code', 'VII')
        ->assertJsonPath('data.sequence', 7);

    $levelId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.levels.update', $levelId), [
        'name' => 'Kelas VII MTs',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Tingkat kelas berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Kelas VII MTs')
        ->assertJsonPath('data.status', 'active');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.levels.store'), [
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII Duplikat',
        'sequence' => 8,
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable();
});

it('membuat dan memperbarui rombel melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'kelas_rombel.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $references = seedKelasRombelMutationReferences();
    $curriculum = AcademicCurriculumRecord::query()->create([
        'code' => 'KUR-2026',
        'name' => 'Kurikulum 2026',
        'status' => 'active',
    ]);
    $level = ClassLevelRecord::query()->create([
        'unit_id' => $references['unit_id'],
        'code' => 'VII',
        'name' => 'Kelas VII',
        'sequence' => 7,
        'status' => 'active',
    ]);

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.store'), [
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'curriculum_id' => $curriculum->id,
        'class_level_id' => $level->id,
        'code' => 'VII-A',
        'name' => 'VII A',
        'capacity' => 32,
        'status' => 'draft',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Rombel berhasil dibuat.')
        ->assertJsonPath('data.code', 'VII-A')
        ->assertJsonPath('data.capacity', 32);

    $classGroupId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.class-groups.update', $classGroupId), [
        'name' => 'VII A Putra',
        'capacity' => 34,
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Rombel berhasil diperbarui.')
        ->assertJsonPath('data.name', 'VII A Putra')
        ->assertJsonPath('data.status', 'active');

    $this->actingAs($actor)->postJson(route('api.v1.academic.class-groups.store'), [
        'academic_year_id' => $references['year_id'],
        'academic_term_id' => $references['term_id'],
        'unit_id' => $references['unit_id'],
        'class_level_id' => $level->id,
        'code' => 'VII-A',
        'name' => 'VII A Duplikat',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable();
});

it('menolak actor tanpa permission kelas_rombel manage untuk mutation', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->postJson(route('api.v1.academic.class-groups.curricula.store'), [
            'code' => 'KUR-2026',
            'name' => 'Kurikulum 2026',
            'status' => 'draft',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

/** @return array{unit_id: string, year_id: string, term_id: string} */
function seedKelasRombelMutationReferences(): array
{
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T34AB1',
        'code' => 'MTS-MUT',
        'name' => 'MTs Mutasi',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T34AC1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T34AC2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T34AC1',
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
        'unit_id' => '01K41KRG60H6GTYB56B6T34AB1',
        'year_id' => '01K41KRG60H6GTYB56B6T34AC1',
        'term_id' => '01K41KRG60H6GTYB56B6T34AC2',
    ];
}
