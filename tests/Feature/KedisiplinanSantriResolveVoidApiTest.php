<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class KedisiplinanSantriResolveVoidApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_kasus_action_assigned_menyimpan_actor_waktu_revision_dan_audit(): void
    {
        $actor = $this->resolver();
        $references = $this->seedReferences();
        $case = $this->actionAssignedCase($references);
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.resolve', $case->id), [
            'resolution_note' => 'Santri sudah menyelesaikan refleksi tertulis dan piket pembinaan.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Kasus kedisiplinan santri berhasil diselesaikan.')
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.resolved_by', $actor->id)
            ->assertJsonPath('data.resolution_note', 'Santri sudah menyelesaikan refleksi tertulis dan piket pembinaan.')
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.needs_action', false)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJson(fn ($json) => $json->whereType('data.resolved_at', 'string')->etc());

        $audit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.resolved')->firstOrFail();

        self::assertSame($actor->id, $audit->actor_id);
        self::assertSame('student_discipline_case', $audit->subject_type);
        self::assertSame($case->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame(['status', 'resolved_at', 'resolved_by', 'resolution_note'], $audit->metadata['changed_fields']);
        self::assertSame('resolved', $audit->metadata['result']['status']);
    }

    public function test_void_kasus_non_final_tidak_menghapus_data_dan_menyimpan_alasan(): void
    {
        $actor = $this->resolver();
        $references = $this->seedReferences();
        $case = $this->submittedCase($references);
        $correlationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.void', $case->id), [
            'void_reason' => 'Laporan duplikat dan sudah digabung ke kasus lain.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $correlationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Kasus kedisiplinan santri berhasil dibatalkan.')
            ->assertJsonPath('data.status', 'void')
            ->assertJsonPath('data.voided_by', $actor->id)
            ->assertJsonPath('data.void_reason', 'Laporan duplikat dan sudah digabung ke kasus lain.')
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJson(fn ($json) => $json->whereType('data.voided_at', 'string')->etc());

        $this->assertDatabaseHas('student_discipline_cases', [
            'id' => $case->id,
            'case_no' => $case->case_no,
            'status' => 'void',
            'voided_by' => $actor->id,
        ]);

        $audit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.voided')->firstOrFail();

        self::assertSame($actor->id, $audit->actor_id);
        self::assertSame($case->id, $audit->subject_id);
        self::assertSame($correlationId, $audit->correlation_id);
        self::assertSame(['status', 'voided_at', 'voided_by', 'void_reason'], $audit->metadata['changed_fields']);
        self::assertSame('void', $audit->metadata['result']['status']);
    }

    public function test_resolve_wajib_catatan_dan_hanya_dari_action_assigned(): void
    {
        $actor = $this->resolver();
        $references = $this->seedReferences();
        $submitted = $this->submittedCase($references);
        $actionAssigned = $this->actionAssignedCase($references, caseNo: 'DIS-RSV-003');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.resolve', $actionAssigned->id), [
            'resolution_note' => '',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['resolution_note']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.resolve', $submitted->id), [
            'resolution_note' => 'Mencoba resolve sebelum ada tindakan.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_void_wajib_alasan_dan_menolak_status_final(): void
    {
        $actor = $this->resolver();
        $references = $this->seedReferences();
        $submitted = $this->submittedCase($references);
        $resolved = $this->resolvedCase($references);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.void', $submitted->id), [
            'void_reason' => '',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['void_reason']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.void', $resolved->id), [
            'void_reason' => 'Mencoba membatalkan status final.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_menolak_actor_tanpa_permission_resolve(): void
    {
        $actor = User::factory()->create();
        $references = $this->seedReferences();
        $case = $this->actionAssignedCase($references);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.resolve', $case->id), [
            'resolution_note' => 'Tanpa akses resolve.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function resolver(): User
    {
        $resolve = Permission::create(['name' => 'kedisiplinan_santri.resolve', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'kedisiplinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$resolve, $view]);

        return $actor;
    }

    /** @param array{student_id: string, employee_id: string} $references */
    private function submittedCase(array $references, string $caseNo = 'DIS-RSV-001'): StudentDisciplineCaseRecord
    {
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'RSV-'.$caseNo,
            'name' => 'Resolve Kedisiplinan '.$caseNo,
        ]);

        return StudentDisciplineCaseRecord::factory()->create([
            'case_no' => $caseNo,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-RSV-001',
            'student_name' => 'Ahmad Resolve',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'submitted',
            'submitted_at' => '2026-09-23 08:00:00',
        ]);
    }

    /** @param array{student_id: string, employee_id: string} $references */
    private function actionAssignedCase(array $references, string $caseNo = 'DIS-RSV-002'): StudentDisciplineCaseRecord
    {
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ACT-'.$caseNo,
            'name' => 'Action Kedisiplinan '.$caseNo,
        ]);

        return StudentDisciplineCaseRecord::factory()->create([
            'case_no' => $caseNo,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-RSV-001',
            'student_name' => 'Ahmad Resolve',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'assigned_employee_id' => $references['employee_id'],
            'assigned_employee_name' => 'Ustadz Rafi Pembina',
            'status' => 'action_assigned',
            'submitted_at' => '2026-09-23 08:00:00',
            'reviewed_at' => '2026-09-23 09:00:00',
            'review_note' => 'Kasus sudah direview.',
            'action_plan' => 'Refleksi tertulis dan pembinaan adab.',
            'action_assigned_at' => '2026-09-23 10:00:00',
        ]);
    }

    /** @param array{student_id: string, employee_id: string} $references */
    private function resolvedCase(array $references): StudentDisciplineCaseRecord
    {
        $case = $this->actionAssignedCase($references, caseNo: 'DIS-RSV-004');
        $case->forceFill([
            'status' => 'resolved',
            'resolved_at' => '2026-09-23 16:00:00',
            'resolution_note' => 'Kasus sudah selesai.',
        ])->save();

        return $case->refresh();
    }

    /** @return array{student_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $studentId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-RSV-001',
            'full_name' => 'Ahmad Resolve',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-RSV-001',
            'name' => 'Ustadz Rafi Pembina',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'student_id' => $studentId,
            'employee_id' => $employeeId,
        ];
    }
}
