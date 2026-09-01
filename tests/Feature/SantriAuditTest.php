<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SantriAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_mencatat_audit_create_dan_update_santri_manual_dengan_metadata_aman(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);
        $createCorrelationId = (string) Str::ulid();
        $updateCorrelationId = (string) Str::ulid();

        $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.students.store'), [
            'full_name' => 'Audit Santri',
            'gender' => 'male',
            'guardian_name' => 'Audit Wali',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ayah',
            'is_emergency_contact' => true,
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $createCorrelationId,
        ])->assertCreated();

        $studentId = (string) $created->json('data.id');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.students.update', $studentId), [
            'full_name' => 'Audit Santri Baru',
            'guardian_phone' => '089999999999',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $updateCorrelationId,
        ])->assertOk();

        $createAudit = AuditRecord::query()
            ->where('action', 'santri.student.created')
            ->firstOrFail();
        $updateAudit = AuditRecord::query()
            ->where('action', 'santri.student.updated')
            ->firstOrFail();

        self::assertSame(2, AuditRecord::query()->where('module', 'Santri')->count());

        self::assertSame('Santri', $createAudit->module);
        self::assertSame($actor->id, $createAudit->actor_id);
        self::assertSame('student', $createAudit->subject_type);
        self::assertSame($studentId, $createAudit->subject_id);
        self::assertSame($createCorrelationId, $createAudit->correlation_id);
        self::assertEqualsCanonicalizing([
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'previous_school',
            'primary_unit_id',
            'entry_date',
            'guardian_name',
            'guardian_relation',
            'is_emergency_contact',
        ], $createAudit->metadata['changed_fields']);
        self::assertSame([
            'student_no' => 'NIS-0001',
            'full_name' => 'Audit Santri',
            'gender' => 'male',
            'primary_unit_id' => null,
            'entry_date' => now()->toDateString(),
            'status' => 'active',
            'guardian_name' => 'Audit Wali',
            'guardian_relation' => 'ayah',
            'is_emergency_contact' => true,
        ], $createAudit->metadata['result']);

        self::assertSame($updateCorrelationId, $updateAudit->correlation_id);
        self::assertSame(['full_name', 'guardian_phone'], $updateAudit->metadata['changed_fields']);
        self::assertSame('Audit Santri Baru', $updateAudit->metadata['result']['full_name']);

        self::assertArrayNotHasKey('guardian_phone', $createAudit->metadata['result']);
        self::assertArrayNotHasKey('guardian_phone', $updateAudit->metadata['result']);
        self::assertArrayNotHasKey('notes', $createAudit->metadata['result']);
        self::assertArrayNotHasKey('password', $createAudit->metadata);
        self::assertArrayNotHasKey('token', $updateAudit->metadata);
    }

    public function test_update_santri_arsip_tidak_mencatat_audit_baru(): void
    {
        $manage = Permission::create(['name' => 'santri.manage', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($manage);

        $student = StudentRecord::factory()->create([
            'archived_at' => now(),
            'archived_by' => $actor->id,
        ]);

        $this->actingAs($actor)->patchJson(
            route('api.v1.pesantrian.students.update', $student->id),
            ['full_name' => 'Tidak Boleh'],
            ['Idempotency-Key' => (string) Str::ulid()],
        )->assertNotFound();

        self::assertSame(0, AuditRecord::query()->where('module', 'Santri')->count());
    }
}
