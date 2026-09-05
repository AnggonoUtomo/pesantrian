<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PresensiSantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_presensi_dengan_filter_search_pagination_sort_dan_envelope_canonical(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();

        $target = StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-05',
            'context_type' => 'class_group',
            'context_id' => $references['class_group_id'],
            'context_name' => 'Kelas X A',
            'session_code' => 'KBM-PAGI',
            'session_name' => 'KBM Pagi',
            'status' => 'submitted',
            'submitted_at' => '2026-09-05 08:00:00',
        ]);

        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $target->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-API-001',
            'student_name' => 'Ahmad List',
            'status' => 'present',
        ]);

        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $target->id,
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-API-002',
            'student_name' => 'Budi List',
            'status' => 'late',
            'minutes_late' => 15,
        ]);

        StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-06',
            'context_type' => 'activity',
            'context_id' => null,
            'context_name' => 'Kegiatan Umum',
            'session_code' => 'SUBUH',
            'session_name' => 'Presensi Subuh',
            'status' => 'draft',
        ]);

        $query = http_build_query([
            'search' => 'KBM',
            'filter' => [
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-05',
                'context_type' => 'class_group',
                'context_id' => $references['class_group_id'],
                'status' => 'submitted',
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'attendance_date',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-attendances.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar presensi santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.attendance_date', '2026-09-05')
            ->assertJsonPath('data.0.context_type', 'class_group')
            ->assertJsonPath('data.0.context_id', $references['class_group_id'])
            ->assertJsonPath('data.0.context_name', 'Kelas X A')
            ->assertJsonPath('data.0.session_code', 'KBM-PAGI')
            ->assertJsonPath('data.0.session_name', 'KBM Pagi')
            ->assertJsonPath('data.0.status', 'submitted')
            ->assertJsonPath('data.0.summary.total', 2)
            ->assertJsonPath('data.0.summary.present', 1)
            ->assertJsonPath('data.0.summary.late', 1)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'attendance_date',
                    'context_type',
                    'context_id',
                    'context_name',
                    'session_code',
                    'session_name',
                    'status',
                    'submitted_at',
                    'created_at',
                    'updated_at',
                    'summary',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_presensi_dengan_summary_dan_entries(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();

        $session = StudentAttendanceSessionRecord::factory()->create([
            'attendance_date' => '2026-09-05',
            'context_type' => 'class_group',
            'context_id' => $references['class_group_id'],
            'context_name' => 'Kelas X A',
            'session_code' => 'KBM-PAGI',
            'session_name' => 'KBM Pagi',
            'status' => 'submitted',
        ]);

        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $session->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-API-001',
            'student_name' => 'Ahmad List',
            'status' => 'present',
        ]);

        StudentAttendanceEntryRecord::factory()->create([
            'session_id' => $session->id,
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-API-002',
            'student_name' => 'Budi List',
            'status' => 'late',
            'minutes_late' => 15,
            'note' => 'Terlambat apel.',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-attendances.show', $session->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail presensi santri berhasil dibaca.')
            ->assertJsonPath('data.id', $session->id)
            ->assertJsonPath('data.summary.total', 2)
            ->assertJsonPath('data.summary.present', 1)
            ->assertJsonPath('data.summary.late', 1)
            ->assertJsonPath('data.entries.0.student_no', 'NIS-API-001')
            ->assertJsonPath('data.entries.0.student_name', 'Ahmad List')
            ->assertJsonPath('data.entries.0.status', 'present')
            ->assertJsonPath('data.entries.1.student_no', 'NIS-API-002')
            ->assertJsonPath('data.entries.1.status', 'late')
            ->assertJsonPath('data.entries.1.minutes_late', 15)
            ->assertJsonPath('data.entries.1.note', 'Terlambat apel.');
    }

    public function test_menolak_actor_tanpa_permission_presensi_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-attendances.index'))
            ->assertForbidden();
    }

    public function test_mengembalikan_404_untuk_detail_presensi_yang_tidak_ada(): void
    {
        $actor = $this->viewer();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-attendances.show', (string) Str::ulid()))
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'presensi_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        return $actor;
    }

    /** @return array{class_group_id: string, first_student_id: string, second_student_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicTermId = (string) Str::ulid();
        $classLevelId = (string) Str::ulid();
        $classGroupId = (string) Str::ulid();
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA',
            'name' => 'MA Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_years')->insert([
            'id' => $academicYearId,
            'code' => '2026-2027',
            'name' => 'Tahun Ajaran 2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_terms')->insert([
            'id' => $academicTermId,
            'academic_year_id' => $academicYearId,
            'code' => '2026-1',
            'name' => 'Semester Ganjil 2026/2027',
            'sequence' => 1,
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('class_levels')->insert([
            'id' => $classLevelId,
            'unit_id' => $unitId,
            'code' => 'X',
            'name' => 'Kelas X',
            'sequence' => 10,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('class_groups')->insert([
            'id' => $classGroupId,
            'academic_year_id' => $academicYearId,
            'academic_term_id' => $academicTermId,
            'unit_id' => $unitId,
            'class_level_id' => $classLevelId,
            'code' => 'X-A',
            'name' => 'Kelas X A',
            'capacity' => 30,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-API-001',
                'full_name' => 'Ahmad List',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-API-002',
                'full_name' => 'Budi List',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return [
            'class_group_id' => $classGroupId,
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
        ];
    }
}
