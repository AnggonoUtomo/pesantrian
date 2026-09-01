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
