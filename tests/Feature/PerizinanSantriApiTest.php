<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PerizinanSantriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_list_perizinan_dengan_filter_search_pagination_sort_dan_envelope_canonical(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();

        $target = StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-API-001',
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-IZN-001',
            'student_name' => 'Ahmad Izin',
            'permit_type' => 'home_visit',
            'starts_at' => '2026-09-10 08:00:00',
            'ends_at' => '2026-09-10 17:00:00',
            'destination' => 'Rumah wali',
            'reason' => 'Pulang bersama wali.',
            'guardian_name' => 'Siti Aminah',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'status' => 'returned',
            'submitted_at' => '2026-09-09 08:00:00',
            'reviewed_at' => '2026-09-09 10:00:00',
            'review_note' => 'Disetujui wali kelas.',
            'checked_out_at' => '2026-09-10 08:30:00',
            'returned_at' => '2026-09-10 18:30:00',
            'return_note' => 'Kembali terlambat karena macet.',
        ]);
        StudentPermitRevisionRecord::factory()->create([
            'permit_id' => $target->id,
            'reason' => 'Koreksi jam kembali.',
            'changed_at' => '2026-09-10 19:00:00',
            'summary' => ['changed_fields' => ['returned_at']],
        ]);

        StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-API-002',
            'student_id' => $references['second_student_id'],
            'student_no' => 'NIS-IZN-002',
            'student_name' => 'Budi Izin',
            'permit_type' => 'leave',
            'starts_at' => '2026-09-11 08:00:00',
            'ends_at' => '2026-09-11 12:00:00',
            'status' => 'approved',
        ]);

        $query = http_build_query([
            'search' => 'Ahmad',
            'filter' => [
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-10',
                'permit_type' => 'home_visit',
                'status' => 'returned',
                'student_id' => $references['first_student_id'],
                'is_late' => true,
            ],
            'page' => 1,
            'per_page' => 10,
            'sort' => 'starts_at',
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-permits.index').'?'.$query)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Daftar perizinan santri berhasil dibaca.')
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.permit_no', 'IZN-API-001')
            ->assertJsonPath('data.0.student_no', 'NIS-IZN-001')
            ->assertJsonPath('data.0.student_name', 'Ahmad Izin')
            ->assertJsonPath('data.0.permit_type', 'home_visit')
            ->assertJsonPath('data.0.destination', 'Rumah wali')
            ->assertJsonPath('data.0.guardian_name', 'Siti Aminah')
            ->assertJsonPath('data.0.guardian_phone', '081234567890')
            ->assertJsonPath('data.0.status', 'returned')
            ->assertJsonPath('data.0.summary.is_late', true)
            ->assertJsonPath('data.0.summary.is_final', true)
            ->assertJsonPath('data.0.summary.is_active', false)
            ->assertJsonPath('data.0.summary.revision_count', 1)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'permit_no',
                    'student_id',
                    'student_no',
                    'student_name',
                    'permit_type',
                    'starts_at',
                    'ends_at',
                    'destination',
                    'reason',
                    'guardian_name',
                    'guardian_phone',
                    'guardian_relation',
                    'status',
                    'submitted_at',
                    'reviewed_at',
                    'checked_out_at',
                    'returned_at',
                    'summary',
                ]],
                'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_mengembalikan_detail_perizinan_dengan_lifecycle_snapshot_dan_revisions(): void
    {
        $actor = $this->viewer();
        $references = $this->seedReferences();

        $permit = StudentPermitRecord::factory()->create([
            'permit_no' => 'IZN-API-003',
            'student_id' => $references['first_student_id'],
            'student_no' => 'NIS-IZN-001',
            'student_name' => 'Ahmad Izin',
            'permit_type' => 'sick',
            'starts_at' => '2026-09-12 08:00:00',
            'ends_at' => '2026-09-12 17:00:00',
            'reason' => 'Izin sakit.',
            'guardian_name' => 'Siti Aminah',
            'guardian_relation' => 'ibu',
            'status' => 'rejected',
            'submitted_at' => '2026-09-11 08:00:00',
            'reviewed_at' => '2026-09-11 09:00:00',
            'review_note' => 'Perlu pemeriksaan klinik dahulu.',
        ]);
        StudentPermitRevisionRecord::factory()->create([
            'permit_id' => $permit->id,
            'reason' => 'Koreksi alasan izin.',
            'changed_at' => '2026-09-11 08:30:00',
            'summary' => ['changed_fields' => ['reason']],
        ]);

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-permits.show', $permit->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Detail perizinan santri berhasil dibaca.')
            ->assertJsonPath('data.id', $permit->id)
            ->assertJsonPath('data.permit_no', 'IZN-API-003')
            ->assertJsonPath('data.student_name', 'Ahmad Izin')
            ->assertJsonPath('data.guardian_name', 'Siti Aminah')
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.review_note', 'Perlu pemeriksaan klinik dahulu.')
            ->assertJsonPath('data.summary.is_late', false)
            ->assertJsonPath('data.summary.is_final', true)
            ->assertJsonPath('data.summary.revision_count', 1)
            ->assertJsonPath('data.revisions.0.reason', 'Koreksi alasan izin.')
            ->assertJsonPath('data.revisions.0.summary.changed_fields.0', 'reason');
    }

    public function test_menolak_actor_tanpa_permission_perizinan_view(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-permits.index'))
            ->assertForbidden();
    }

    public function test_mengembalikan_404_untuk_detail_perizinan_yang_tidak_ada(): void
    {
        $actor = $this->viewer();

        $this->actingAs($actor)
            ->getJson(route('api.v1.pesantrian.student-permits.show', (string) Str::ulid()))
            ->assertNotFound()
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
    }

    private function viewer(): User
    {
        $view = Permission::create(['name' => 'perizinan_santri.view', 'guard_name' => 'web']);
        $actor = User::factory()->create();
        $actor->givePermissionTo($view);

        return $actor;
    }

    /** @return array{first_student_id: string, second_student_id: string} */
    private function seedReferences(): array
    {
        $firstStudentId = (string) Str::ulid();
        $secondStudentId = (string) Str::ulid();

        DB::table('students')->insert([
            [
                'id' => $firstStudentId,
                'student_no' => 'NIS-IZN-001',
                'full_name' => 'Ahmad Izin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $secondStudentId,
                'student_no' => 'NIS-IZN-002',
                'full_name' => 'Budi Izin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return [
            'first_student_id' => $firstStudentId,
            'second_student_id' => $secondStudentId,
        ];
    }
}
