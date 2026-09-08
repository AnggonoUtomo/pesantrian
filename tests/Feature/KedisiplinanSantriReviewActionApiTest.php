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

final class KedisiplinanSantriReviewActionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_dan_assign_action_kasus_kedisiplinan_dengan_actor_waktu_revision_dan_audit(): void
    {
        $actor = $this->reviewer();
        $references = $this->seedReferences();
        $case = $this->submittedCase($references);
        $reviewCorrelationId = (string) Str::ulid();
        $assignCorrelationId = (string) Str::ulid();

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.review', $case->id), [
            'review_note' => 'Kronologi sudah diklarifikasi dengan musyrif.',
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $reviewCorrelationId,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Kasus kedisiplinan santri berhasil direview.')
            ->assertJsonPath('data.status', 'in_review')
            ->assertJsonPath('data.reviewed_by', $actor->id)
            ->assertJsonPath('data.review_note', 'Kronologi sudah diklarifikasi dengan musyrif.')
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJson(fn ($json) => $json->whereType('data.reviewed_at', 'string')->etc());

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.assign-action', $case->id), [
            'action_plan' => 'Membuat refleksi tertulis dan piket kebersihan masjid selama tiga hari.',
            'assigned_employee_id' => $references['employee_id'],
        ], [
            'Idempotency-Key' => (string) Str::ulid(),
            'X-Correlation-ID' => $assignCorrelationId,
        ])->assertOk()
            ->assertJsonPath('message', 'Tindakan pembinaan kasus kedisiplinan berhasil ditetapkan.')
            ->assertJsonPath('data.status', 'action_assigned')
            ->assertJsonPath('data.assigned_employee_id', $references['employee_id'])
            ->assertJsonPath('data.assigned_employee_name', 'Ustadz Karim Pembina')
            ->assertJsonPath('data.action_plan', 'Membuat refleksi tertulis dan piket kebersihan masjid selama tiga hari.')
            ->assertJsonPath('data.summary.revision_count', 2)
            ->assertJson(fn ($json) => $json->whereType('data.action_assigned_at', 'string')->etc());

        $reviewAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.reviewed')->firstOrFail();
        $assignAudit = AuditRecord::query()->where('action', 'kedisiplinan_santri.case.action_assigned')->firstOrFail();

        self::assertSame($actor->id, $reviewAudit->actor_id);
        self::assertSame($case->id, $reviewAudit->subject_id);
        self::assertSame($reviewCorrelationId, $reviewAudit->correlation_id);
        self::assertSame(['status', 'reviewed_at', 'reviewed_by', 'review_note'], $reviewAudit->metadata['changed_fields']);
        self::assertSame('in_review', $reviewAudit->metadata['result']['status']);

        self::assertSame($actor->id, $assignAudit->actor_id);
        self::assertSame($case->id, $assignAudit->subject_id);
        self::assertSame($assignCorrelationId, $assignAudit->correlation_id);
        self::assertSame(['status', 'action_plan', 'action_assigned_at', 'assigned_employee_id'], $assignAudit->metadata['changed_fields']);
        self::assertSame('action_assigned', $assignAudit->metadata['result']['status']);
    }

    public function test_review_hanya_dari_submitted_dan_assign_action_wajib_catatan(): void
    {
        $actor = $this->reviewer();
        $references = $this->seedReferences();
        $draft = $this->draftCase($references);
        $submitted = $this->submittedCase($references, caseNo: 'DIS-REV-002');

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.review', $draft->id), [
            'review_note' => 'Mencoba review draft.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['status']);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.assign-action', $submitted->id), [
            'action_plan' => '',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['action_plan']);
    }

    public function test_assign_action_menolak_pembina_tidak_aktif(): void
    {
        $actor = $this->reviewer();
        $references = $this->seedReferences();
        $case = $this->submittedCase($references);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.assign-action', $case->id), [
            'action_plan' => 'Pembinaan dengan pembina tidak aktif.',
            'assigned_employee_id' => $references['inactive_employee_id'],
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'KEDISIPLINAN_SANTRI_MUTATION_INVALID')
            ->assertJsonValidationErrors(['assigned_employee_id']);
    }

    public function test_menolak_actor_tanpa_permission_review(): void
    {
        $actor = User::factory()->create();
        $references = $this->seedReferences();
        $case = $this->submittedCase($references);

        $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.student-discipline-cases.review', $case->id), [
            'review_note' => 'Tanpa akses review.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
            ->assertForbidden();
    }

    private function reviewer(): User
    {
        $review = Permission::create(['name' => 'kedisiplinan_santri.review', 'guard_name' => 'web']);
        $view = Permission::create(['name' => 'kedisiplinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo([$review, $view]);

        return $actor;
    }

    /** @param array{student_id: string, employee_id: string} $references */
    private function draftCase(array $references, string $caseNo = 'DIS-REV-000'): StudentDisciplineCaseRecord
    {
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'REVIEW',
            'name' => 'Review Kedisiplinan',
        ]);

        return StudentDisciplineCaseRecord::factory()->create([
            'case_no' => $caseNo,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-REV-001',
            'student_name' => 'Ahmad Review',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'draft',
        ]);
    }

    /** @param array{student_id: string, employee_id: string} $references */
    private function submittedCase(array $references, string $caseNo = 'DIS-REV-001'): StudentDisciplineCaseRecord
    {
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'REVIEW-'.$caseNo,
            'name' => 'Review Kedisiplinan '.$caseNo,
        ]);

        return StudentDisciplineCaseRecord::factory()->create([
            'case_no' => $caseNo,
            'student_id' => $references['student_id'],
            'student_no' => 'NIS-REV-001',
            'student_name' => 'Ahmad Review',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'status' => 'submitted',
            'submitted_at' => '2026-09-22 08:00:00',
        ]);
    }

    /** @return array{student_id: string, employee_id: string, inactive_employee_id: string} */
    private function seedReferences(): array
    {
        $studentId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();
        $inactiveEmployeeId = (string) Str::ulid();

        DB::table('students')->insert([
            'id' => $studentId,
            'student_no' => 'NIS-REV-001',
            'full_name' => 'Ahmad Review',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            [
                'id' => $employeeId,
                'employee_no' => 'PEG-REV-001',
                'name' => 'Ustadz Karim Pembina',
                'employment_type' => 'staff',
                'position' => 'Pembina Kedisiplinan',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $inactiveEmployeeId,
                'employee_no' => 'PEG-REV-099',
                'name' => 'Pembina Nonaktif',
                'employment_type' => 'staff',
                'position' => 'Pembina Lama',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return [
            'student_id' => $studentId,
            'employee_id' => $employeeId,
            'inactive_employee_id' => $inactiveEmployeeId,
        ];
    }
}
