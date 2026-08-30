<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PenerimaanSantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_pendaftaran_santri_dengan_filter_pagination_sort_dan_envelope_canonical(): void
    {
        $view = Permission::create(['name' => 'penerimaan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);
        $unitId = $this->createOrganizationUnit('MTS', 'Madrasah Tsanawiyah');

        $admission = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0001',
            'registration_period' => 'PPDB 2027',
            'candidate_name' => 'Muhammad Fikri',
            'candidate_gender' => 'male',
            'target_unit_id' => $unitId,
            'guardian_name' => 'Ahmad Fadli',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ayah',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'pending',
            'document_checklist' => [
                ['type' => 'kartu_keluarga', 'status' => 'submitted'],
            ],
            'status' => 'submitted',
            'registered_at' => '2026-08-30 08:00:00',
        ]);
        StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0002',
            'candidate_name' => 'Budi Arsip',
            'guardian_name' => 'Wali Arsip',
            'registration_fee_status' => 'not_required',
            'status' => 'draft',
        ]);

        $query = http_build_query([
            'search' => 'Fikri',
            'filter' => [
                'status' => 'submitted',
                'target_unit_id' => $unitId,
                'registration_fee_status' => 'pending',
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'registration_no',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.admissions.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar pendaftaran santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $admission->id)
            ->assertJsonPath('data.0.registration_no', 'SNTR-0001')
            ->assertJsonPath('data.0.candidate_name', 'Muhammad Fikri')
            ->assertJsonPath('data.0.target_unit_id', $unitId)
            ->assertJsonPath('data.0.guardian_name', 'Ahmad Fadli')
            ->assertJsonPath('data.0.registration_fee_required', true)
            ->assertJsonPath('data.0.registration_fee_amount', '250000.00')
            ->assertJsonPath('data.0.registration_fee_status', 'pending')
            ->assertJsonPath('data.0.document_checklist.0.type', 'kartu_keluarga')
            ->assertJsonPath('data.0.status', 'submitted')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'registration_no',
                    'registration_period',
                    'candidate_name',
                    'candidate_gender',
                    'candidate_birth_place',
                    'candidate_birth_date',
                    'previous_school',
                    'target_unit_id',
                    'guardian_name',
                    'guardian_phone',
                    'guardian_relation',
                    'registration_fee_required',
                    'registration_fee_amount',
                    'registration_fee_status',
                    'document_checklist',
                    'status',
                    'registered_at',
                    'decided_at',
                    'decided_by',
                    'notes',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_membuat_dan_memperbarui_pendaftaran_santri_melalui_api_terotorisasi(): void
    {
        $manage = Permission::create(['name' => 'penerimaan_santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);
        $unitId = $this->createOrganizationUnit('MA', 'Madrasah Aliyah');

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.admissions.store'), [
            'registration_period' => 'PPDB 2027',
            'candidate_name' => 'Aisyah Humaira',
            'candidate_gender' => 'female',
            'candidate_birth_place' => 'Garut',
            'candidate_birth_date' => '2013-04-12',
            'previous_school' => 'SD Negeri 2',
            'target_unit_id' => $unitId,
            'guardian_name' => 'Siti Aminah',
            'guardian_phone' => '081298765432',
            'guardian_relation' => 'ibu',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'pending',
            'document_checklist' => [
                ['type' => 'akta_kelahiran', 'status' => 'submitted'],
            ],
            'status' => 'submitted',
            'notes' => 'Calon santri gelombang pertama.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pendaftaran santri berhasil dibuat.')
            ->assertJsonPath('data.registration_no', 'SNTR-0001')
            ->assertJsonPath('data.candidate_name', 'Aisyah Humaira')
            ->assertJsonPath('data.registration_fee_amount', '250000.00');

        $admissionId = (string) $created->json('data.id');

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.admissions.store'), [
            'candidate_name' => 'Hasan Basri',
            'guardian_name' => 'Abdullah',
            'registration_fee_status' => 'not_required',
            'status' => 'draft',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertCreated()
            ->assertJsonPath('data.registration_no', 'SNTR-0002');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.update', $admissionId), [
            'candidate_name' => 'Aisyah Saka',
            'registration_fee_status' => 'verified',
            'document_checklist' => [
                ['type' => 'akta_kelahiran', 'status' => 'verified'],
            ],
            'notes' => 'Berkas awal lengkap.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pendaftaran santri berhasil diperbarui.')
            ->assertJsonPath('data.registration_no', 'SNTR-0001')
            ->assertJsonPath('data.candidate_name', 'Aisyah Saka')
            ->assertJsonPath('data.registration_fee_status', 'verified')
            ->assertJsonPath('data.document_checklist.0.status', 'verified');

        self::assertTrue(DB::table('student_admissions')
            ->where('id', $admissionId)
            ->where('registration_no', 'SNTR-0001')
            ->where('candidate_name', 'Aisyah Saka')
            ->where('registration_fee_status', 'verified')
            ->exists());
    }

    public function test_memproses_lifecycle_pendaftaran_santri_melalui_api_terotorisasi(): void
    {
        $decide = Permission::create(['name' => 'penerimaan_santri.decide', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($decide);

        $submitted = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0101',
            'candidate_name' => 'Nadia Salma',
            'guardian_name' => 'Ahmad Salim',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'submitted',
            'registered_at' => now(),
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.admissions.verify', $submitted->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pendaftaran santri berhasil diverifikasi.')
            ->assertJsonPath('data.status', 'verified')
            ->assertJsonPath('data.decided_by', $actor->id);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.admissions.accept', $submitted->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('message', 'Pendaftaran santri berhasil diterima.')
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.decided_by', $actor->id);

        self::assertTrue(DB::table('student_admissions')
            ->where('id', $submitted->id)
            ->where('status', 'accepted')
            ->where('decided_by', $actor->id)
            ->whereNotNull('decided_at')
            ->exists());

        $verified = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0102',
            'candidate_name' => 'Hasan Ridwan',
            'guardian_name' => 'Rudi Hartono',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.admissions.reject', $verified->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('message', 'Pendaftaran santri berhasil ditolak.')
            ->assertJsonPath('data.status', 'rejected');

        $draft = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0103',
            'candidate_name' => 'Alya Putri',
            'guardian_name' => 'Siti Halimah',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'draft',
            'registered_at' => now(),
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.admissions.cancel', $draft->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertOk()
            ->assertJsonPath('message', 'Pendaftaran santri berhasil dibatalkan.')
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_menolak_lifecycle_tidak_valid_terminal_state_dan_actor_tanpa_permission(): void
    {
        $decide = Permission::create(['name' => 'penerimaan_santri.decide', 'guard_name' => 'web']);
        $manage = Permission::create(['name' => 'penerimaan_santri.manage', 'guard_name' => 'web']);

        $decider = User::factory()->create();
        $decider->givePermissionTo($decide);

        $manager = User::factory()->create();
        $manager->givePermissionTo($manage);

        $submitted = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0201',
            'candidate_name' => 'Fikri Rahman',
            'guardian_name' => 'Rahman Hakim',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'submitted',
        ]);

        $this->actingAs($manager)->patchJson(
            route('api.v1.pesantrian.admissions.verify', $submitted->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');

        $this->actingAs($decider)->patchJson(
            route('api.v1.pesantrian.admissions.accept', $submitted->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['status']]);

        $accepted = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0202',
            'candidate_name' => 'Dimas Putra',
            'guardian_name' => 'Soleh Hidayat',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $decider->id,
        ]);

        $this->actingAs($decider)->patchJson(
            route('api.v1.pesantrian.admissions.cancel', $accepted->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonPath('errors.status.0', 'Status accepted bersifat terminal dan tidak dapat diproses lagi.');

        $this->actingAs($manager)->patchJson(
            route('api.v1.pesantrian.admissions.update', $accepted->id),
            ['candidate_name' => 'Dimas Revisi'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonPath('errors.status.0', 'Status accepted bersifat terminal dan tidak dapat diperbarui.');

        $this->actingAs($decider)->patchJson(
            route('api.v1.pesantrian.admissions.verify', (string) Str::ulid()),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    public function test_menolak_guest_actor_tanpa_permission_payload_invalid_dan_resource_tidak_ditemukan(): void
    {
        $this->getJson(route('api.v1.pesantrian.admissions.index'))
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        $unauthorized = User::factory()->create();
        $this->actingAs($unauthorized)
            ->getJson(route('api.v1.pesantrian.admissions.index'))
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN');

        $manage = Permission::create(['name' => 'penerimaan_santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);

        $this->actingAs($actor)->postJson(route('api.v1.pesantrian.admissions.store'), [
            'registration_no' => 'MANUAL-001',
            'candidate_name' => 'A',
            'candidate_gender' => 'unknown',
            'target_unit_id' => (string) Str::ulid(),
            'guardian_name' => '',
            'registration_fee_required' => true,
            'registration_fee_status' => 'verified',
            'document_checklist' => [['type' => '', 'status' => 'unknown']],
            'status' => 'accepted',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'errors' => [
                    'registration_no',
                    'candidate_name',
                    'candidate_gender',
                    'target_unit_id',
                    'guardian_name',
                    'registration_fee_amount',
                    'document_checklist.0.type',
                    'document_checklist.0.status',
                    'status',
                ],
            ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.admissions.update', (string) Str::ulid()),
            ['candidate_name' => 'Missing'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function createOrganizationUnit(string $code, string $name): string
    {
        $id = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
