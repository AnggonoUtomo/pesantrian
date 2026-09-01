<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\AcceptedAdmissionReader;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\AcceptedAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

final class SantriAdmissionConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengonversi_pendaftaran_accepted_menjadi_data_induk_santri(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);
        $unitId = $this->createOrganizationUnit('MTS-CNV', 'Madrasah Tsanawiyah');
        $correlationId = (string) Str::ulid();

        $admission = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9001',
            'registration_period' => 'PPDB 2027',
            'candidate_name' => 'Muhammad Fikri',
            'candidate_gender' => 'male',
            'candidate_birth_place' => 'Bandung',
            'candidate_birth_date' => '2013-05-10',
            'previous_school' => 'SD Negeri 1',
            'target_unit_id' => $unitId,
            'guardian_name' => 'Ahmad Fadli',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ayah',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'verified',
            'status' => 'accepted',
            'registered_at' => now(),
            'decided_at' => now(),
            'decided_by' => $actor->id,
        ]);

        $response = $this->actingAs($actor)->postJson(
            route('api.v1.pesantrian.students.from-admission', $admission->id),
            [],
            [
                'Idempotency-Key' => (string) Str::ulid(),
                'X-Correlation-ID' => $correlationId,
            ],
        );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pendaftaran diterima berhasil dikonversi menjadi santri.')
            ->assertJsonPath('data.student_no', 'NIS-0001')
            ->assertJsonPath('data.admission_id', $admission->id)
            ->assertJsonPath('data.registration_no', 'SNTR-9001')
            ->assertJsonPath('data.full_name', 'Muhammad Fikri')
            ->assertJsonPath('data.gender', 'male')
            ->assertJsonPath('data.birth_place', 'Bandung')
            ->assertJsonPath('data.birth_date', '2013-05-10')
            ->assertJsonPath('data.previous_school', 'SD Negeri 1')
            ->assertJsonPath('data.primary_unit_id', $unitId)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.primary_guardian.guardian_name', 'Ahmad Fadli')
            ->assertJsonPath('data.primary_guardian.guardian_phone', '081234567890')
            ->assertJsonPath('data.primary_guardian.guardian_relation', 'ayah');

        $studentId = (string) $response->json('data.id');

        self::assertTrue(DB::table('students')
            ->where('id', $studentId)
            ->where('student_no', 'NIS-0001')
            ->where('admission_id', $admission->id)
            ->where('registration_no', 'SNTR-9001')
            ->where('full_name', 'Muhammad Fikri')
            ->where('primary_unit_id', $unitId)
            ->exists());

        self::assertTrue(DB::table('student_guardians')
            ->where('student_id', $studentId)
            ->where('guardian_name', 'Ahmad Fadli')
            ->where('guardian_phone', '081234567890')
            ->where('guardian_relation', 'ayah')
            ->where('is_primary', true)
            ->exists());

        $audit = AuditRecord::query()
            ->where('action', 'santri.student.created_from_admission')
            ->firstOrFail();

        self::assertSame('Santri', $audit->module);
        self::assertSame($actor->id, $audit->actor_id);
        self::assertSame('student', $audit->subject_type);
        self::assertSame($studentId, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('NIS-0001', $audit->metadata['result']['student_no']);
        self::assertSame($admission->id, $audit->metadata['result']['admission_id']);
        self::assertSame('SNTR-9001', $audit->metadata['result']['registration_no']);
        self::assertArrayNotHasKey('guardian_phone', $audit->metadata['result']);
        self::assertArrayNotHasKey('document_checklist', $audit->metadata);
    }

    public function test_menolak_pendaftaran_yang_tidak_eligible_untuk_dikonversi(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);

        $pendingPayment = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9002',
            'candidate_name' => 'Aisyah Humaira',
            'guardian_name' => 'Siti Aminah',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'pending',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $actor->id,
        ]);

        $this->actingAs($actor)->postJson(
            route('api.v1.pesantrian.students.from-admission', $pendingPayment->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['admission']]);

        self::assertSame(0, StudentRecord::query()->count());
    }

    public function test_menolak_konversi_pendaftaran_yang_sudah_menjadi_santri(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);

        $admission = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9003',
            'candidate_name' => 'Hasan Basri',
            'guardian_name' => 'Abdullah',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $actor->id,
        ]);

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-0099',
            'admission_id' => $admission->id,
            'registration_no' => 'SNTR-9003',
            'full_name' => 'Hasan Basri',
        ]);

        StudentGuardianRecord::query()->create([
            'student_id' => $student->id,
            'guardian_name' => 'Abdullah',
            'is_primary' => true,
            'is_emergency_contact' => false,
        ]);

        $this->actingAs($actor)->postJson(
            route('api.v1.pesantrian.students.from-admission', $admission->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonPath('errors.admission.0', 'Pendaftaran ini sudah dikonversi menjadi santri.');

        self::assertSame(1, StudentRecord::query()->where('admission_id', $admission->id)->count());
    }

    public function test_menolak_actor_tanpa_permission_konversi_santri(): void
    {
        $actor = User::factory()->create();

        $admission = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9004',
            'candidate_name' => 'Nadia Salma',
            'guardian_name' => 'Ahmad Salim',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $actor->id,
        ]);

        $this->actingAs($actor)->postJson(
            route('api.v1.pesantrian.students.from-admission', $admission->id),
            [],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
    }

    public function test_contract_accepted_admission_menyaring_data_eligible_dan_boundary_santri_tidak_bocor(): void
    {
        $decider = User::factory()->create();

        $accepted = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9005',
            'candidate_name' => 'Dimas Putra',
            'candidate_gender' => 'male',
            'candidate_birth_date' => '2013-01-20',
            'guardian_name' => 'Soleh Hidayat',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'accepted',
            'decided_at' => now(),
            'decided_by' => $decider->id,
        ]);

        $submitted = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-9006',
            'candidate_name' => 'Belum Diterima',
            'guardian_name' => 'Wali Belum',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'submitted',
        ]);

        $reader = $this->app->make(AcceptedAdmissionReader::class);

        $data = $reader->findAcceptedForConversion($accepted->id);

        self::assertInstanceOf(AcceptedAdmissionData::class, $data);
        self::assertSame($accepted->id, $data->admissionId);
        self::assertSame('SNTR-9005', $data->registrationNo);
        self::assertSame('Dimas Putra', $data->candidateName);
        self::assertSame('2013-01-20', $data->candidateBirthDate);
        self::assertSame($decider->id, $data->acceptedBy);
        self::assertNull($reader->findAcceptedForConversion($submitted->id));

        $santriSource = '';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path('Modules/Pesantrian/Santri')),
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $santriSource .= file_get_contents($file->getPathname()) ?: '';
        }

        self::assertStringNotContainsString('PenerimaanSantri\\Infrastructure', $santriSource);
        self::assertStringNotContainsString('StudentAdmissionRecord', $santriSource);
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
