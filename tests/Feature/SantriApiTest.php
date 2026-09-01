<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_santri_dengan_filter_search_pagination_sort_dan_envelope_canonical(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);
        $unitId = $this->createOrganizationUnit('MTS-S', 'Madrasah Tsanawiyah');

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-0001',
            'full_name' => 'Muhammad Fikri',
            'preferred_name' => 'Fikri',
            'gender' => 'male',
            'birth_place' => 'Bandung',
            'birth_date' => '2013-05-10',
            'previous_school' => 'SD Negeri 1',
            'primary_unit_id' => $unitId,
            'entry_date' => '2026-07-15',
            'status' => 'active',
        ]);

        StudentGuardianRecord::query()->create([
            'student_id' => $student->id,
            'guardian_name' => 'Ahmad Fadli',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ayah',
            'is_primary' => true,
            'is_emergency_contact' => true,
        ]);

        StudentRecord::factory()->create([
            'student_no' => 'NIS-0002',
            'full_name' => 'Budi Tidak Aktif',
            'primary_unit_id' => $unitId,
            'status' => 'inactive',
        ]);

        $query = http_build_query([
            'search' => 'Fikri',
            'filter' => [
                'status' => 'active',
                'primary_unit_id' => $unitId,
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'student_no',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $student->id)
            ->assertJsonPath('data.0.student_no', 'NIS-0001')
            ->assertJsonPath('data.0.full_name', 'Muhammad Fikri')
            ->assertJsonPath('data.0.preferred_name', 'Fikri')
            ->assertJsonPath('data.0.gender', 'male')
            ->assertJsonPath('data.0.birth_place', 'Bandung')
            ->assertJsonPath('data.0.birth_date', '2013-05-10')
            ->assertJsonPath('data.0.previous_school', 'SD Negeri 1')
            ->assertJsonPath('data.0.primary_unit_id', $unitId)
            ->assertJsonPath('data.0.entry_date', '2026-07-15')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.0.primary_guardian.guardian_name', 'Ahmad Fadli')
            ->assertJsonPath('data.0.primary_guardian.guardian_phone', '081234567890')
            ->assertJsonPath('data.0.primary_guardian.guardian_relation', 'ayah')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'student_no',
                    'admission_id',
                    'registration_no',
                    'full_name',
                    'preferred_name',
                    'gender',
                    'birth_place',
                    'birth_date',
                    'previous_school',
                    'primary_unit_id',
                    'entry_date',
                    'status',
                    'primary_guardian',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_santri_dengan_snapshot_wali(): void
    {
        $view = Permission::create(['name' => 'santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-0003',
            'full_name' => 'Aisyah Zahra',
            'status' => 'active',
        ]);

        StudentGuardianRecord::query()->create([
            'student_id' => $student->id,
            'guardian_name' => 'Siti Aminah',
            'guardian_phone' => '081298765432',
            'guardian_relation' => 'ibu',
            'is_primary' => true,
            'is_emergency_contact' => false,
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.show', $student->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail santri berhasil dibaca.')
            ->assertJsonPath('data.id', $student->id)
            ->assertJsonPath('data.student_no', 'NIS-0003')
            ->assertJsonPath('data.full_name', 'Aisyah Zahra')
            ->assertJsonPath('data.guardians.0.guardian_name', 'Siti Aminah')
            ->assertJsonPath('data.guardians.0.is_primary', true)
            ->assertJsonPath('data.guardians.0.is_emergency_contact', false);
    }

    public function test_membuat_dan_memperbarui_santri_manual_melalui_api_terotorisasi(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);
        $unitId = $this->createOrganizationUnit('MA-S', 'Madrasah Aliyah');

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.students.store'), [
            'full_name' => 'Aisyah Humaira',
            'preferred_name' => 'Aisyah',
            'gender' => 'female',
            'birth_place' => 'Garut',
            'birth_date' => '2013-04-12',
            'previous_school' => 'SD Negeri 2',
            'primary_unit_id' => $unitId,
            'entry_date' => '2026-07-15',
            'guardian_name' => 'Siti Aminah',
            'guardian_phone' => '081298765432',
            'guardian_relation' => 'ibu',
            'is_emergency_contact' => true,
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data santri berhasil dibuat.')
            ->assertJsonPath('data.student_no', 'NIS-0001')
            ->assertJsonPath('data.full_name', 'Aisyah Humaira')
            ->assertJsonPath('data.primary_guardian.guardian_name', 'Siti Aminah')
            ->assertJsonPath('data.primary_guardian.is_primary', true)
            ->assertJsonPath('data.primary_guardian.is_emergency_contact', true);

        $studentId = (string) $created->json('data.id');

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.students.store'), [
            'full_name' => 'Hasan Basri',
            'guardian_name' => 'Abdullah',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertCreated()
            ->assertJsonPath('data.student_no', 'NIS-0002');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.students.update', $studentId), [
            'full_name' => 'Aisyah Saka',
            'preferred_name' => null,
            'guardian_name' => 'Siti Aminah Baru',
            'guardian_phone' => '089999999999',
            'guardian_relation' => 'wali',
            'is_emergency_contact' => false,
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data santri berhasil diperbarui.')
            ->assertJsonPath('data.student_no', 'NIS-0001')
            ->assertJsonPath('data.full_name', 'Aisyah Saka')
            ->assertJsonPath('data.preferred_name', null)
            ->assertJsonPath('data.primary_guardian.guardian_name', 'Siti Aminah Baru')
            ->assertJsonPath('data.primary_guardian.guardian_phone', '089999999999')
            ->assertJsonPath('data.primary_guardian.guardian_relation', 'wali')
            ->assertJsonPath('data.primary_guardian.is_emergency_contact', false);

        self::assertTrue(DB::table('students')
            ->where('id', $studentId)
            ->where('student_no', 'NIS-0001')
            ->where('full_name', 'Aisyah Saka')
            ->where('primary_unit_id', $unitId)
            ->exists());

        self::assertTrue(DB::table('student_guardians')
            ->where('student_id', $studentId)
            ->where('guardian_name', 'Siti Aminah Baru')
            ->where('guardian_phone', '089999999999')
            ->where('is_primary', true)
            ->exists());
    }

    public function test_menolak_create_update_santri_tanpa_permission_payload_invalid_dan_resource_tidak_ditemukan(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->postJson(route('api.v1.pesantrian.students.store'), [
                'full_name' => 'Aisyah',
                'guardian_name' => 'Siti',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $manager = User::factory()->create();
        $manager->givePermissionTo($manage);

        $this->actingAs($manager)->postJson(route('api.v1.pesantrian.students.store'), [
            'student_no' => 'MANUAL-001',
            'full_name' => 'A',
            'gender' => 'unknown',
            'primary_unit_id' => (string) Str::ulid(),
            'guardian_name' => '',
            'guardian_relation' => 'saudara',
            'status' => 'graduated',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'errors' => [
                    'student_no',
                    'full_name',
                    'gender',
                    'primary_unit_id',
                    'guardian_name',
                    'guardian_relation',
                    'status',
                ],
            ]);

        $this->actingAs($manager)->patchJson(
            route('api.v1.pesantrian.students.update', (string) Str::ulid()),
            ['full_name' => 'Missing'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    public function test_menolak_actor_tanpa_permission_santri_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.students.index'))
            ->assertForbidden();
    }

    private function createOrganizationUnit(string $code, string $name): string
    {
        $id = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $id,
            'parent_id' => null,
            'code' => $code,
            'name' => $name,
            'type' => 'education_unit',
            'status' => 'active',
            'location_name' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
