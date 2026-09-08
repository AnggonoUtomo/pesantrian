<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class KedisiplinanSantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_kategori_kedisiplinan(): void
    {
        $actor = $this->viewer();

        $active = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'TERLAMBAT',
            'name' => 'Terlambat Kegiatan',
            'description' => 'Kategori untuk keterlambatan kegiatan wajib.',
            'default_severity' => 'minor',
            'default_points' => 5,
            'status' => 'active',
        ]);
        StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ARSIP',
            'name' => 'Kategori Arsip',
            'status' => 'archived',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-categories.index').'?'.http_build_query([
                'search' => 'terlambat',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar kategori kedisiplinan santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.code', 'TERLAMBAT')
            ->assertJsonPath('data.0.default_points', 5)
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'code',
                    'name',
                    'description',
                    'default_severity',
                    'default_points',
                    'status',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['correlation_id'],
            ]);
    }

    public function test_mengembalikan_list_kasus_kedisiplinan_dengan_filter_search_pagination_sort_dan_envelope_canonical(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'TERLAMBAT',
            'name' => 'Terlambat Kegiatan',
            'default_severity' => 'minor',
            'default_points' => 5,
        ]);

        $target = StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-API-001',
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-DIS-001',
            'student_name' => 'Ahmad Disiplin',
            'unit_id' => $references['unit_id'],
            'unit_name' => 'MTs Saka',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => 'minor',
            'points' => 5,
            'occurred_at' => '2026-09-16 07:30:00',
            'location' => 'Masjid',
            'description' => 'Terlambat mengikuti kegiatan pagi.',
            'assigned_employee_id' => $references['employee_id'],
            'assigned_employee_name' => 'Ustadz Karim Pembina',
            'status' => 'action_assigned',
            'submitted_at' => '2026-09-16 08:00:00',
            'reviewed_at' => '2026-09-16 08:30:00',
            'review_note' => 'Perlu pembinaan ringan.',
            'action_plan' => 'Menghafal doa setelah shalat.',
            'action_assigned_at' => '2026-09-16 09:00:00',
        ]);
        StudentDisciplineRevisionRecord::factory()->create([
            'case_id' => $target->id,
            'reason' => 'Menambahkan tindakan pembinaan.',
            'changed_at' => '2026-09-16 09:00:00',
            'summary' => ['changed_fields' => ['action_plan']],
        ]);

        StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-API-002',
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-DIS-002',
            'student_name' => 'Budi Disiplin',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => 'major',
            'occurred_at' => '2026-09-18 10:00:00',
            'status' => 'submitted',
        ]);

        $query = http_build_query([
            'search' => 'Ahmad',
            'filter' => [
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-16',
                'status' => 'action_assigned',
                'severity' => 'minor',
                'category_id' => $category->id,
                'student_id' => $references['first_student_id'],
                'assigned_employee_id' => $references['employee_id'],
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'occurred_at',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-cases.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar kasus kedisiplinan santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.case_no', 'DIS-API-001')
            ->assertJsonPath('data.0.student_no', 'NIS-DIS-001')
            ->assertJsonPath('data.0.student_name', 'Ahmad Disiplin')
            ->assertJsonPath('data.0.unit_name', 'MTs Saka')
            ->assertJsonPath('data.0.category.name', 'Terlambat Kegiatan')
            ->assertJsonPath('data.0.severity', 'minor')
            ->assertJsonPath('data.0.points', 5)
            ->assertJsonPath('data.0.location', 'Masjid')
            ->assertJsonPath('data.0.status', 'action_assigned')
            ->assertJsonPath('data.0.action_plan', 'Menghafal doa setelah shalat.')
            ->assertJsonPath('data.0.summary.is_final', false)
            ->assertJsonPath('data.0.summary.revision_count', 1)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'case_no',
                    'student_id',
                    'student_no',
                    'student_name',
                    'unit_id',
                    'unit_name',
                    'category',
                    'severity',
                    'points',
                    'occurred_at',
                    'location',
                    'description',
                    'assigned_employee_id',
                    'assigned_employee_name',
                    'status',
                    'summary',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_kasus_kedisiplinan_dengan_lifecycle_snapshot_action_resolution_dan_revisions(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $category = StudentDisciplineCategoryRecord::factory()->create([
            'code' => 'ADAB',
            'name' => 'Adab dan Ketertiban',
            'default_severity' => 'moderate',
            'default_points' => 15,
        ]);

        $case = StudentDisciplineCaseRecord::factory()->create([
            'case_no' => 'DIS-API-003',
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-DIS-001',
            'student_name' => 'Ahmad Disiplin',
            'unit_id' => $references['unit_id'],
            'unit_name' => 'MTs Saka',
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => 'moderate',
            'points' => 15,
            'occurred_at' => '2026-09-17 13:00:00',
            'location' => 'Asrama Putra',
            'description' => 'Melanggar adab kebersihan asrama.',
            'reported_by' => $actor->id,
            'assigned_employee_id' => $references['employee_id'],
            'assigned_employee_name' => 'Ustadz Karim Pembina',
            'status' => 'resolved',
            'submitted_at' => '2026-09-17 13:30:00',
            'reviewed_at' => '2026-09-17 14:00:00',
            'reviewed_by' => $actor->id,
            'review_note' => 'Valid setelah klarifikasi.',
            'action_plan' => 'Piket kebersihan asrama selama tiga hari.',
            'action_assigned_at' => '2026-09-17 14:30:00',
            'resolved_at' => '2026-09-20 16:00:00',
            'resolved_by' => $actor->id,
            'resolution_note' => 'Santri menyelesaikan pembinaan.',
        ]);
        StudentDisciplineRevisionRecord::factory()->create([
            'case_id' => $case->id,
            'reason' => 'Kasus disubmit untuk review.',
            'changed_at' => '2026-09-17 13:30:00',
            'summary' => ['changed_fields' => ['status'], 'to_status' => 'submitted'],
        ]);
        StudentDisciplineRevisionRecord::factory()->create([
            'case_id' => $case->id,
            'reason' => 'Kasus diselesaikan.',
            'changed_at' => '2026-09-20 16:00:00',
            'summary' => ['changed_fields' => ['status', 'resolution_note'], 'to_status' => 'resolved'],
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-cases.show', $case->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail kasus kedisiplinan santri berhasil dibaca.')
            ->assertJsonPath('data.id', $case->id)
            ->assertJsonPath('data.case_no', 'DIS-API-003')
            ->assertJsonPath('data.student_name', 'Ahmad Disiplin')
            ->assertJsonPath('data.unit_name', 'MTs Saka')
            ->assertJsonPath('data.category.code', 'ADAB')
            ->assertJsonPath('data.category.default_points', 15)
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.review_note', 'Valid setelah klarifikasi.')
            ->assertJsonPath('data.action_plan', 'Piket kebersihan asrama selama tiga hari.')
            ->assertJsonPath('data.resolution_note', 'Santri menyelesaikan pembinaan.')
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 2)
            ->assertJsonPath('data.revisions.0.reason', 'Kasus disubmit untuk review.')
            ->assertJsonPath('data.revisions.1.summary.to_status', 'resolved');
    }

    public function test_menolak_actor_tanpa_permission_kedisiplinan_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-cases.index'))
            ->assertForbidden();
    }

    public function test_mengembalikan_404_untuk_detail_kasus_kedisiplinan_yang_tidak_ada(): void
    {
        $actor = $this->viewer();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-discipline-cases.show', (string) Str::ulid()))
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'kedisiplinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        return $actor;
    }

    /** @return array{first_student_id: string, second_student_id: string, unit_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();
        $unitId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MTD',
            'name' => 'MTs Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-DIS-001',
                'full_name' => 'Ahmad Disiplin',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-DIS-002',
                'full_name' => 'Budi Disiplin',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-DIS-101',
            'name' => 'Ustadz Karim Pembina',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
            'unit_id' => $unitId,
            'employee_id' => $employeeId,
        ];
    }
}
