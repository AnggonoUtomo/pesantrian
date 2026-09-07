<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PerizinanSantriOperationalLifecycleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_mencatat_santri_keluar_dari_status_approved_dengan_revision_dan_audit(): void
    {
        $actor = $this->actor(['perizinan_santri.checkout']);
        $permit = $this->permit('approved');
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.checkout', $permit->id), [], [
                'Idempotency-Key' => (string) Str::ulid(),
                'X-Correlation-ID' => $correlationId,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Izin santri berhasil di-check-out.')
            ->assertJsonPath('data.status', 'checked_out')
            ->assertJsonPath('data.checked_out_by', $actor->id)
            ->assertJsonPath('data.summary.is_active', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.summary.action', 'checkout')
            ->assertJsonPath('data.revisions.0.summary.from_status', 'approved')
            ->assertJsonPath('data.revisions.0.summary.to_status', 'checked_out');

        $this->assertDatabaseHas('student_permits', [
            'id' => $permit->id,
            'status' => 'checked_out',
            'checked_out_by' => $actor->id,
        ]);

        $audit = AuditRecord::query()->where('action', 'perizinan_santri.permit.checked_out')->firstOrFail();

        self::assertSame('PerizinanSantri', $audit->module);
        self::assertSame('student_permit', $audit->subject_type);
        self::assertSame($permit->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame('approved', $audit->metadata['from_status']);
        self::assertSame('checked_out', $audit->metadata['to_status']);
        self::assertSame('checked_out', $audit->metadata['result']['status']);
    }

    public function test_return_mencatat_santri_kembali_dan_keterlambatan_terbaca_di_read_model(): void
    {
        $actor = $this->actor(['perizinan_santri.return']);
        $permit = $this->permit('checked_out', [
            'checked_out_at' => '2026-09-20 08:00:00',
            'ends_at' => '2026-09-20 17:00:00',
        ]);

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.return', $permit->id), [
                'returned_at' => '2026-09-20 18:30:00',
                'return_note' => 'Santri kembali terlambat karena macet.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('message', 'Kepulangan santri berhasil dicatat.')
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.returned_by', $actor->id)
            ->assertJsonPath('data.return_note', 'Santri kembali terlambat karena macet.')
            ->assertJsonPath('data.summary.is_late', true)
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.revisions.0.summary.action', 'return')
            ->assertJsonPath('data.revisions.0.summary.is_late', true);

        $this->actingAs($this->actor(['perizinan_santri.view']))
            ->getJson(route('api.v1.pesantrian.student-permits.show', $permit->id))
            ->assertOk()
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.summary.is_late', true);

        expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
            ->toContain('perizinan_santri.permit.returned');
    }

    public function test_void_membatalkan_izin_non_final_dengan_alasan_tanpa_menghapus_data(): void
    {
        $actor = $this->actor(['perizinan_santri.archive']);
        $permit = $this->permit('submitted');
        $reason = 'Permohonan salah input dan diganti dengan data baru.';

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.void', $permit->id), [
                'reason' => $reason,
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertOk()
            ->assertJsonPath('message', 'Izin santri berhasil dibatalkan.')
            ->assertJsonPath('data.status', 'void')
            ->assertJsonPath('data.voided_by', $actor->id)
            ->assertJsonPath('data.void_reason', $reason)
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', $reason)
            ->assertJsonPath('data.revisions.0.summary.action', 'void')
            ->assertJsonPath('data.revisions.0.summary.from_status', 'submitted')
            ->assertJsonPath('data.revisions.0.summary.to_status', 'void');

        self::assertSame(1, StudentPermitRecord::query()->whereKey($permit->id)->count());
        expect(AuditRecord::query()->where('module', 'PerizinanSantri')->pluck('action')->all())
            ->toContain('perizinan_santri.permit.voided');
    }

    public function test_menolak_lifecycle_dari_status_yang_tidak_sesuai_dan_void_wajib_alasan(): void
    {
        $checkoutActor = $this->actor(['perizinan_santri.checkout']);
        $returnActor = $this->actor(['perizinan_santri.return']);
        $archiveActor = $this->actor(['perizinan_santri.archive']);
        $draft = $this->permit('draft');
        $approved = $this->permit('approved');
        $returned = $this->permit('returned', [
            'checked_out_at' => '2026-09-20 08:00:00',
            'returned_at' => '2026-09-20 16:00:00',
        ]);

        $this->actingAs($checkoutActor)
            ->patchJson(route('api.v1.pesantrian.student-permits.checkout', $draft->id), [], [
                'Idempotency-Key' => (string) Str::ulid(),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

        $this->actingAs($returnActor)
            ->patchJson(route('api.v1.pesantrian.student-permits.return', $approved->id), [
                'returned_at' => '2026-09-20 16:00:00',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

        $this->actingAs($archiveActor)
            ->patchJson(route('api.v1.pesantrian.student-permits.void', $returned->id), [
                'reason' => 'Tidak boleh membatalkan izin yang sudah final.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'PERIZINAN_SANTRI_MUTATION_INVALID');

        $this->actingAs($archiveActor)
            ->patchJson(route('api.v1.pesantrian.student-permits.void', $approved->id), [
                'reason' => 'x',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_menolak_actor_tanpa_permission_operasional(): void
    {
        $actor = User::factory()->create();
        $approved = $this->permit('approved');
        $checkedOut = $this->permit('checked_out', [
            'checked_out_at' => '2026-09-20 08:00:00',
        ]);
        $submitted = $this->permit('submitted');

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.checkout', $approved->id), [], [
                'Idempotency-Key' => (string) Str::ulid(),
            ])
            ->assertForbidden();

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.return', $checkedOut->id), [
                'returned_at' => '2026-09-20 16:00:00',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();

        $this->actingAs($actor)
            ->patchJson(route('api.v1.pesantrian.student-permits.void', $submitted->id), [
                'reason' => 'Tidak punya akses.',
            ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    /** @param list<string> $permissions */
    private function actor(array $permissions): User
    {
        $actor = User::factory()->create();

        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }

        return $actor;
    }

    /** @param array<string, mixed> $overrides */
    private function permit(string $status, array $overrides = []): StudentPermitRecord
    {
        $student = $this->student();

        return StudentPermitRecord::factory()->create([
            'student_id' => $student['id'],
            'student_no' => $student['student_no'],
            'student_name' => $student['full_name'],
            'permit_type' => 'home_visit',
            'starts_at' => '2026-09-20 08:00:00',
            'ends_at' => '2026-09-20 17:00:00',
            'destination' => 'Rumah wali',
            'reason' => 'Keperluan keluarga.',
            'status' => $status,
            'submitted_at' => $status === 'draft' ? null : '2026-09-19 08:00:00',
            ...$overrides,
        ]);
    }

    /** @return array{id: string, student_no: string, full_name: string} */
    private function student(): array
    {
        $unitId = (string) Str::ulid();
        $studentId = (string) Str::ulid();
        $studentNo = 'NIS-OPS-'.substr($studentId, -6);
        $fullName = 'Ahmad Operational Perizinan';

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA-'.substr($unitId, -6),
            'name' => 'MA Operational Perizinan',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => $studentNo,
            'full_name' => $fullName,
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $studentId,
            'student_no' => $studentNo,
            'full_name' => $fullName,
        ];
    }
}
