<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TahfidzApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_setoran_tahfidz_dengan_filter_pagination_sort_dan_envelope_canonical(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create([
            'code' => 'THF-REG',
            'name' => 'Tahfidz Reguler',
        ]);
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-THF-001',
            'student_name' => 'Ahmad Hafidz',
            'academic_period_id' => $references['academic_term_id'],
            'period_label' => 'Semester Ganjil 2026/2027',
            'target_juz' => 1,
            'status' => 'active',
        ]);
        $submission = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-THF-001',
            'student_name' => 'Ahmad Hafidz',
            'supervisor_id' => $references['employee_id'],
            'supervisor_name' => 'Ustadz Hasan Tahfidz',
            'submission_date' => '2026-09-07',
            'type' => 'new_memorization',
            'juz' => 1,
            'surah' => 'Al-Baqarah',
            'ayah_from' => 1,
            'ayah_to' => 5,
            'status' => 'submitted',
            'quality_note' => 'Setoran awal.',
        ]);

        TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-THF-002',
            'student_name' => 'Budi Murojaah',
            'submission_date' => '2026-09-08',
            'type' => 'murojaah',
            'status' => 'accepted',
        ]);

        $query = http_build_query([
            'search' => 'Ahmad',
            'filter' => [
                'program_id' => $program->id,
                'student_id' => $references['first_student_id'],
                'supervisor_id' => $references['employee_id'],
                'academic_period_id' => $references['academic_term_id'],
                'type' => 'new_memorization',
                'status' => 'submitted',
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-07',
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'submission_date',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.tahfidz.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar setoran tahfidz berhasil dibaca.')
            ->assertJsonPath('data.0.id', $submission->id)
            ->assertJsonPath('data.0.program.code', 'THF-REG')
            ->assertJsonPath('data.0.program.name', 'Tahfidz Reguler')
            ->assertJsonPath('data.0.target.id', $target->id)
            ->assertJsonPath('data.0.target.period_label', 'Semester Ganjil 2026/2027')
            ->assertJsonPath('data.0.student_no', 'NIS-THF-001')
            ->assertJsonPath('data.0.student_name', 'Ahmad Hafidz')
            ->assertJsonPath('data.0.supervisor_name', 'Ustadz Hasan Tahfidz')
            ->assertJsonPath('data.0.submission_date', '2026-09-07')
            ->assertJsonPath('data.0.type', 'new_memorization')
            ->assertJsonPath('data.0.status', 'submitted')
            ->assertJsonPath('data.0.summary.has_target', true)
            ->assertJsonPath('data.0.summary.has_supervisor', true)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'program',
                    'target',
                    'student_id',
                    'student_no',
                    'student_name',
                    'supervisor_id',
                    'supervisor_name',
                    'submission_date',
                    'type',
                    'juz',
                    'surah',
                    'ayah_from',
                    'ayah_to',
                    'status',
                    'quality_note',
                    'summary',
                    'created_at',
                    'updated_at',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_setoran_tahfidz_dengan_target_pembimbing_summary_dan_revisi(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();
        $program = TahfidzProgramRecord::factory()->create([
            'code' => 'THF-INT',
            'name' => 'Tahfidz Intensif',
        ]);
        $target = TahfidzTargetRecord::factory()->create([
            'program_id' => $program->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-THF-001',
            'student_name' => 'Ahmad Hafidz',
            'academic_period_id' => $references['academic_term_id'],
            'period_label' => 'Semester Ganjil 2026/2027',
            'target_juz' => 2,
            'target_surah' => 'Ali Imran',
            'target_ayah_from' => 1,
            'target_ayah_to' => 20,
        ]);
        $submission = TahfidzSubmissionRecord::factory()->create([
            'program_id' => $program->id,
            'target_id' => $target->id,
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-THF-001',
            'student_name' => 'Ahmad Hafidz',
            'supervisor_id' => $references['employee_id'],
            'supervisor_name' => 'Ustadz Hasan Tahfidz',
            'submission_date' => '2026-09-07',
            'type' => 'murojaah',
            'juz' => 2,
            'surah' => 'Ali Imran',
            'ayah_from' => 1,
            'ayah_to' => 10,
            'status' => 'needs_revision',
            'quality_note' => 'Perlu pengulangan ayat akhir.',
        ]);
        TahfidzSubmissionRevisionRecord::factory()->create([
            'submission_id' => $submission->id,
            'reason' => 'Koreksi status review.',
            'changed_at' => '2026-09-07 10:00:00',
            'summary' => ['status' => 'needs_revision'],
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.tahfidz.show', $submission->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail setoran tahfidz berhasil dibaca.')
            ->assertJsonPath('data.id', $submission->id)
            ->assertJsonPath('data.program.name', 'Tahfidz Intensif')
            ->assertJsonPath('data.target.target_surah', 'Ali Imran')
            ->assertJsonPath('data.target.target_ayah_to', 20)
            ->assertJsonPath('data.student_name', 'Ahmad Hafidz')
            ->assertJsonPath('data.supervisor_name', 'Ustadz Hasan Tahfidz')
            ->assertJsonPath('data.type', 'murojaah')
            ->assertJsonPath('data.status', 'needs_revision')
            ->assertJsonPath('data.summary.has_revision', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', 'Koreksi status review.')
            ->assertJsonPath('data.revisions.0.summary.status', 'needs_revision');
    }

    public function test_menolak_actor_tanpa_permission_tahfidz_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.tahfidz.index'))
            ->assertForbidden();
    }

    public function test_mengembalikan_404_untuk_detail_setoran_tahfidz_yang_tidak_ada(): void
    {
        $actor = $this->viewer();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.tahfidz.show', (string) Str::ulid()))
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'tahfidz.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        return $actor;
    }

    /** @return array{first_student_id: string, second_student_id: string, academic_term_id: string, employee_id: string} */
    private function seedReferences(): array
    {
        $unitId = (string) Str::ulid();
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();
        $academicYearId = (string) Str::ulid();
        $academicTermId = (string) Str::ulid();
        $employeeId = (string) Str::ulid();

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'code' => 'MA',
            'name' => 'MA Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-THF-001',
                'full_name' => 'Ahmad Hafidz',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-THF-002',
                'full_name' => 'Budi Murojaah',
                'primary_unit_id' => $unitId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
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

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'PEG-THF-001',
            'name' => 'Ustadz Hasan Tahfidz',
            'employment_type' => 'teacher',
            'position' => 'Pembimbing Tahfidz',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
            'academic_term_id' => $academicTermId,
            'employee_id' => $employeeId,
        ];
    }
}
