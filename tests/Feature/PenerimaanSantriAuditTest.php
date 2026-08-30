<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PenerimaanSantriAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_mencatat_audit_create_dan_update_pendaftaran_santri_dengan_metadata_aman(): void
    {
        $manage = Permission::create(['name' => 'penerimaan_santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.admissions.store'), [
            'registration_period' => 'PPDB 2027',
            'candidate_name' => 'Audit Santri',
            'candidate_gender' => 'male',
            'guardian_name' => 'Audit Wali',
            'guardian_phone' => '081234567890',
            'registration_fee_required' => true,
            'registration_fee_amount' => 250000,
            'registration_fee_status' => 'pending',
            'document_checklist' => [
                ['type' => 'akta_kelahiran', 'status' => 'submitted', 'notes' => 'Ada catatan dokumen.'],
            ],
            'status' => 'submitted',
            'notes' => 'Catatan internal tidak perlu masuk audit.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated();

        $admissionId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.update', $admissionId), [
            'candidate_name' => 'Audit Santri Baru',
            'guardian_phone' => '089999999999',
            'registration_fee_status' => 'verified',
            'notes' => 'Catatan update tidak perlu masuk audit.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk();

        $createAudit = AuditRecord::query()
            ->where('action', 'penerimaan_santri.registration.created')
            ->firstOrFail();
        $updateAudit = AuditRecord::query()
            ->where('action', 'penerimaan_santri.registration.updated')
            ->firstOrFail();

        self::assertSame(2, AuditRecord::query()->where('module', 'PenerimaanSantri')->count());

        self::assertSame('PenerimaanSantri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student_admission', $createAudit->subject_type);
        self::assertSame($admissionId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertEqualsCanonicalizing([
            'registration_period',
            'candidate_name',
            'candidate_gender',
            'candidate_birth_place',
            'candidate_birth_date',
            'previous_school',
            'target_unit_id',
            'guardian_name',
            'guardian_relation',
            'registration_fee_required',
            'registration_fee_amount',
            'registration_fee_status',
            'document_checklist',
            'status',
        ], $createAudit->metadata['changed_fields']);
        self::assertSame([
            'registration_no' => 'SNTR-0001',
            'candidate_name' => 'Audit Santri',
            'candidate_gender' => 'male',
            'target_unit_id' => null,
            'guardian_name' => 'Audit Wali',
            'guardian_relation' => null,
            'registration_fee_required' => true,
            'registration_fee_amount' => '250000.00',
            'registration_fee_status' => 'pending',
            'status' => 'submitted',
        ], $createAudit->metadata['result']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame(['candidate_name', 'guardian_phone', 'registration_fee_status', 'notes'], $updateAudit->metadata['changed_fields']);
        self::assertSame('submitted', $updateAudit->metadata['to_status']);
        self::assertSame('Audit Santri Baru', $updateAudit->metadata['result']['candidate_name']);
        self::assertSame('verified', $updateAudit->metadata['result']['registration_fee_status']);

        self::assertArrayNotHasKey('guardian_phone', $createAudit->metadata['result']);
        self::assertArrayNotHasKey('notes', $createAudit->metadata['result']);
        self::assertArrayNotHasKey('document_checklist', $createAudit->metadata['result']);
        self::assertArrayNotHasKey('guardian_phone', $updateAudit->metadata['result']);
        self::assertArrayNotHasKey('notes', $updateAudit->metadata['result']);
        self::assertArrayNotHasKey('password', $createAudit->metadata);
        self::assertArrayNotHasKey('token', $updateAudit->metadata);
    }

    public function test_mencatat_audit_lifecycle_pendaftaran_santri_dengan_metadata_aman(): void
    {
        $decide = Permission::create(['name' => 'penerimaan_santri.decide', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($decide);
        $verifyCorrelationId = (string) Str::ulid();
        $acceptCorrelationId = (string) Str::ulid();
        $rejectCorrelationId = (string) Str::ulid();
        $cancelCorrelationId = (string) Str::ulid();

        $acceptedFlow = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0301',
            'candidate_name' => 'Accepted Audit',
            'guardian_name' => 'Wali Accepted',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'submitted',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.verify', $acceptedFlow->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $verifyCorrelationId,
        ])->assertOk();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.accept', $acceptedFlow->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $acceptCorrelationId,
        ])->assertOk();

        $rejectedFlow = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0302',
            'candidate_name' => 'Rejected Audit',
            'guardian_name' => 'Wali Rejected',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'verified',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.reject', $rejectedFlow->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $rejectCorrelationId,
        ])->assertOk();

        $cancelledFlow = StudentAdmissionRecord::query()->create([
            'registration_no' => 'SNTR-0303',
            'candidate_name' => 'Cancelled Audit',
            'guardian_name' => 'Wali Cancelled',
            'registration_fee_required' => false,
            'registration_fee_status' => 'not_required',
            'status' => 'draft',
        ]);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.admissions.cancel', $cancelledFlow->id), [], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $cancelCorrelationId,
        ])->assertOk();

        self::assertSame(4, AuditRecord::query()->where('module', 'PenerimaanSantri')->count());

        $verifyAudit = AuditRecord::query()->where('action', 'penerimaan_santri.registration.verified')->firstOrFail();
        $acceptAudit = AuditRecord::query()->where('action', 'penerimaan_santri.registration.accepted')->firstOrFail();
        $rejectAudit = AuditRecord::query()->where('action', 'penerimaan_santri.registration.rejected')->firstOrFail();
        $cancelAudit = AuditRecord::query()->where('action', 'penerimaan_santri.registration.cancelled')->firstOrFail();

        self::assertSame($actor->id, $verifyAudit->actor_id);
        self::assertSame('student_admission', $verifyAudit->subject_type);
        self::assertSame($acceptedFlow->id, $verifyAudit->subject_id);
        self::assertSame($verifyCorrelationId, $verifyAudit->correlation_id);
        self::assertSame(['status', 'decided_at', 'decided_by'], $verifyAudit->metadata['changed_fields']);
        self::assertSame('verified', $verifyAudit->metadata['to_status']);
        self::assertSame('verified', $verifyAudit->metadata['result']['status']);

        self::assertSame($acceptCorrelationId, $acceptAudit->correlation_id);
        self::assertSame('accepted', $acceptAudit->metadata['to_status']);
        self::assertSame('accepted', $acceptAudit->metadata['result']['status']);

        self::assertSame($rejectCorrelationId, $rejectAudit->correlation_id);
        self::assertSame($rejectedFlow->id, $rejectAudit->subject_id);
        self::assertSame('rejected', $rejectAudit->metadata['to_status']);

        self::assertSame($cancelCorrelationId, $cancelAudit->correlation_id);
        self::assertSame($cancelledFlow->id, $cancelAudit->subject_id);
        self::assertSame('cancelled', $cancelAudit->metadata['to_status']);

        self::assertArrayNotHasKey('guardian_phone', $verifyAudit->metadata['result']);
        self::assertArrayNotHasKey('notes', $acceptAudit->metadata['result']);
        self::assertArrayNotHasKey('document_checklist', $rejectAudit->metadata['result']);
        self::assertArrayNotHasKey('token', $cancelAudit->metadata);
    }
}
