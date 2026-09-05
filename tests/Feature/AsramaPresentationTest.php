<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AsramaPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_menolak_actor_tanpa_permission_asrama_view(): void
    {
        $actor = $this->createUser();

        $this->actingAs($actor)
            ->get(route('pesantrian.asrama.index'))
            ->assertForbidden();
    }

    public function test_menampilkan_halaman_inertia_daftar_asrama(): void
    {
        $view = Permission::create(['name' => 'asrama.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $fixture = $this->createAsramaFixture();

        $this->actingAs($actor)
            ->get(route('pesantrian.asrama.index', [
                'search' => 'Putra',
                'filter' => [
                    'unit_id' => $fixture['unitId'],
                    'gender_policy' => 'male',
                    'status' => 'active',
                ],
                'per_page' => 10,
                'sort' => 'code',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Asrama/pages/Index')
                ->where('dormitories.data.0.code', 'ASR-UI-PUTRA')
                ->where('dormitories.data.0.name', 'Asrama UI Putra')
                ->where('dormitories.data.0.unit.id', $fixture['unitId'])
                ->where('dormitories.data.0.gender_policy', 'male')
                ->where('dormitories.data.0.room_count', 1)
                ->where('dormitories.data.0.capacity', 8)
                ->where('dormitories.data.0.occupied_count', 1)
                ->where('dormitories.data.0.available_capacity', 7)
                ->where('dormitories.meta.total', 1)
                ->where('filters.search', 'Putra')
                ->where('filters.filter.unit_id', $fixture['unitId'])
                ->where('filters.filter.gender_policy', 'male')
                ->where('filters.filter.status', 'active')
                ->where('filters.per_page', '10')
                ->where('filters.sort', 'code')
                ->where('pagination.defaultPerPage', 25)
                ->where('options.units.0.id', $fixture['unitId'])
                ->where('options.units.0.name', 'Asrama UI')
                ->where('canManage', false)
                ->where('canPlacement', false)
                ->where('canSupervisor', false)
                ->where('canArchive', false));
    }

    public function test_menampilkan_halaman_inertia_detail_asrama(): void
    {
        $view = Permission::create(['name' => 'asrama.view', 'guard_name' => 'web']);
        $actor = $this->createUser();
        $actor->givePermissionTo($view);
        $fixture = $this->createAsramaFixture(withDetails: true);

        $this->actingAs($actor)
            ->get(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Pesantrian/Asrama/pages/Show')
                ->where('dormitory.code', 'ASR-UI-PUTRA')
                ->where('dormitory.name', 'Asrama UI Putra')
                ->where('dormitory.rooms.0.code', 'KMR-UI-P01')
                ->where('dormitory.rooms.0.occupied_count', 1)
                ->where('dormitory.placements.0.student_no', 'NIS-ASRAMA-UI')
                ->where('dormitory.placements.0.student_name', 'Santri Asrama UI')
                ->where('dormitory.supervisors.0.employee_name', 'Ustaz Asrama UI')
                ->where('canManage', false)
                ->where('canPlacement', false)
                ->where('canSupervisor', false)
                ->where('canArchive', false));
    }

    public function test_web_mutation_ui_asrama_memakai_action_application(): void
    {
        $permissions = [
            'asrama.view',
            'asrama.manage',
            'asrama.placement',
            'asrama.supervisor',
            'asrama.archive',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $actor = $this->createUser();
        $actor->givePermissionTo($permissions);
        $fixture = $this->createAsramaFixture(withDetails: true, withSecondRoom: true);

        $this->actingAs($actor)
            ->post(route('pesantrian.asrama.store'), [
                'unit_id' => $fixture['unitId'],
                'code' => 'ASR-WEB-BARU',
                'name' => 'Asrama Web Baru',
                'gender_policy' => 'mixed',
                'description' => 'Dibuat dari UI web.',
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $createdDormitoryId = (string) DB::table('dormitories')->where('code', 'ASR-WEB-BARU')->value('id');

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.update', $createdDormitoryId), [
                'unit_id' => $fixture['unitId'],
                'code' => 'ASR-WEB-EDIT',
                'name' => 'Asrama Web Edit',
                'gender_policy' => 'mixed',
                'description' => 'Diperbarui dari UI web.',
                'status' => 'active',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->post(route('pesantrian.asrama.rooms.store', $createdDormitoryId), [
                'code' => 'KMR-WEB-01',
                'name' => 'Kamar Web 01',
                'capacity' => 4,
                'status' => 'active',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $createdRoomId = (string) DB::table('dormitory_rooms')->where('code', 'KMR-WEB-01')->value('id');

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.rooms.update', [$createdDormitoryId, $createdRoomId]), [
                'code' => 'KMR-WEB-01A',
                'name' => 'Kamar Web 01A',
                'capacity' => 5,
                'status' => 'active',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-ASRAMA-WEB',
            'full_name' => 'Santri Asrama Web',
            'gender' => 'male',
            'primary_unit_id' => $fixture['unitId'],
            'status' => 'active',
        ]);

        $this->actingAs($actor)
            ->post(route('pesantrian.asrama.placements.store', $fixture['dormitoryId']), [
                'student_id' => $student->id,
                'dormitory_room_id' => $fixture['roomId'],
                'started_at' => '2026-08-01',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertSessionHasNoErrors();

        $placementId = (string) DB::table('student_room_placements')->where('student_id', $student->id)->value('id');

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.placements.transfer', [$fixture['dormitoryId'], $placementId]), [
                'target_room_id' => $fixture['secondRoomId'],
                'started_at' => '2026-08-02',
                'reason' => 'Pindah kamar untuk uji web.',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertSessionHasNoErrors();

        $currentPlacementId = (string) DB::table('student_room_placements')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->value('id');

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.placements.remove', [$fixture['dormitoryId'], $currentPlacementId]), [
                'ended_at' => '2026-08-03',
                'reason' => 'Keluar kamar untuk uji web.',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertSessionHasNoErrors();

        $employee = EmployeeRecord::query()->create([
            'employee_no' => 'PEG-ASRAMA-WEB',
            'name' => 'Ustaz Asrama Web',
            'preferred_name' => 'Ustaz Web',
            'employment_type' => 'teacher',
            'primary_unit_id' => $fixture['unitId'],
            'position' => 'Musyrif Web',
            'status' => 'active',
            'joined_on' => '2026-07-01',
            'left_on' => null,
            'notes' => null,
        ]);

        $this->actingAs($actor)
            ->post(route('pesantrian.asrama.supervisors.store', $fixture['dormitoryId']), [
                'employee_id' => $employee->id,
                'dormitory_room_id' => $fixture['roomId'],
                'role' => 'musyrif',
                'started_at' => '2026-08-01',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertSessionHasNoErrors();

        $assignmentId = (string) DB::table('dormitory_supervisor_assignments')->where('employee_id', $employee->id)->value('id');

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.supervisors.end', [$fixture['dormitoryId'], $assignmentId]), [
                'ended_at' => '2026-08-10',
                'reason' => 'Selesai tugas untuk uji web.',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $fixture['dormitoryId']))
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.rooms.archive', [$createdDormitoryId, $createdRoomId]), [
                'reason' => 'Kamar diarsipkan dari UI web.',
            ])
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.rooms.restore', [$createdDormitoryId, $createdRoomId]))
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.archive', $createdDormitoryId), [
                'reason' => 'Asrama diarsipkan dari UI web.',
            ])
            ->assertRedirect(route('pesantrian.asrama.index'))
            ->assertSessionHasNoErrors();

        $this->actingAs($actor)
            ->patch(route('pesantrian.asrama.restore', $createdDormitoryId))
            ->assertRedirect(route('pesantrian.asrama.show', $createdDormitoryId))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('dormitories', [
            'id' => $createdDormitoryId,
            'code' => 'ASR-WEB-EDIT',
            'status' => 'active',
            'archived_at' => null,
        ]);
        $this->assertDatabaseHas('dormitory_rooms', [
            'id' => $createdRoomId,
            'code' => 'KMR-WEB-01A',
            'capacity' => 5,
            'archived_at' => null,
        ]);
        $this->assertDatabaseHas('student_room_placements', [
            'id' => $currentPlacementId,
            'student_id' => $student->id,
            'status' => 'inactive',
            'reason' => 'Keluar kamar untuk uji web.',
        ]);
        $this->assertDatabaseHas('dormitory_supervisor_assignments', [
            'id' => $assignmentId,
            'status' => 'ended',
            'reason' => 'Selesai tugas untuk uji web.',
        ]);
    }

    public function test_menghubungkan_ui_asrama_ke_komponen_canonical_dan_sidebar(): void
    {
        $index = $this->sourceFile('js/pages/Pesantrian/Asrama/pages/Index.tsx');
        $dashboard = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaDashboard.tsx');
        $show = $this->sourceFile('js/pages/Pesantrian/Asrama/pages/Show.tsx');
        $filters = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaFilters.tsx');
        $table = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaTable.tsx');
        $summary = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaSummaryCards.tsx');
        $pagination = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaPagination.tsx');
        $detail = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaDetailPanel.tsx');
        $mutation = $this->sourceFile('js/pages/Pesantrian/Asrama/components/AsramaMutationDialogs.tsx');
        $navigation = $this->sourceFile('js/lib/navigation.ts');

        self::assertStringContainsString('AsramaDashboard', $index);
        self::assertStringContainsString("canAccess(auth, 'asrama.view')", $dashboard);
        self::assertStringContainsString('AsramaSummaryCards', $dashboard);
        self::assertStringContainsString('AsramaFilters', $dashboard);
        self::assertStringContainsString('AsramaTable', $dashboard);
        self::assertStringContainsString('AsramaPagination', $dashboard);
        self::assertStringContainsString('Cari asrama', $filters);
        self::assertStringContainsString('Status asrama', $filters);
        self::assertStringContainsString('Status arsip', $filters);
        self::assertStringContainsString('Unit asrama', $filters);
        self::assertStringContainsString('Asrama', $table);
        self::assertStringContainsString('Hunian', $table);
        self::assertStringContainsString('Lihat detail', $table);
        self::assertStringContainsString('Total asrama', $summary);
        self::assertStringContainsString('Kamar aktif', $summary);
        self::assertStringContainsString('Sebelumnya', $pagination);
        self::assertStringContainsString('Berikutnya', $pagination);
        self::assertStringContainsString('Daftar kamar', $detail);
        self::assertStringContainsString('Penempatan santri', $detail);
        self::assertStringContainsString('Musyrif / pembina', $detail);
        self::assertStringContainsString('Tambah asrama', $mutation);
        self::assertStringContainsString('Tambah kamar', $mutation);
        self::assertStringContainsString('Tempatkan santri', $mutation);
        self::assertStringContainsString('Pindah kamar', $mutation);
        self::assertStringContainsString('Keluarkan santri', $mutation);
        self::assertStringContainsString('Tugaskan musyrif', $mutation);
        self::assertStringContainsString('Akhiri tugas musyrif', $mutation);
        self::assertStringContainsString('Arsipkan asrama', $detail);
        self::assertStringContainsString('Pulihkan asrama', $detail);
        self::assertStringContainsString('pesantrian.asrama.store', $mutation);
        self::assertStringContainsString('pesantrian.asrama.rooms.store', $mutation);
        self::assertStringContainsString('pesantrian.asrama.placements.store', $mutation);
        self::assertStringContainsString('pesantrian.asrama.supervisors.store', $mutation);
        self::assertStringContainsString('Asrama', $show);
        self::assertStringContainsString('Asrama', $navigation);
        self::assertStringContainsString('pesantrian.asrama.index', $navigation);
        self::assertStringContainsString("'asrama.view'", $navigation);
    }

    /** @return array{unitId: string, dormitoryId: string, roomId: string, secondRoomId: string|null} */
    private function createAsramaFixture(bool $withDetails = false, bool $withSecondRoom = false): array
    {
        $now = now();
        $unitId = (string) Str::ulid();
        $dormitoryId = (string) Str::ulid();
        $roomId = (string) Str::ulid();
        $secondRoomId = $withSecondRoom ? (string) Str::ulid() : null;

        DB::table('organization_units')->insert([
            'id' => $unitId,
            'parent_id' => null,
            'code' => 'ASR-UI',
            'name' => 'Asrama UI',
            'type' => 'dormitory',
            'status' => 'active',
            'location_name' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('dormitories')->insert([
            'id' => $dormitoryId,
            'unit_id' => $unitId,
            'code' => 'ASR-UI-PUTRA',
            'name' => 'Asrama UI Putra',
            'gender_policy' => 'male',
            'description' => 'Asrama untuk pengujian UI.',
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('dormitory_rooms')->insert([
            'id' => $roomId,
            'dormitory_id' => $dormitoryId,
            'code' => 'KMR-UI-P01',
            'name' => 'Kamar UI Putra 01',
            'capacity' => 8,
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        if ($secondRoomId !== null) {
            DB::table('dormitory_rooms')->insert([
                'id' => $secondRoomId,
                'dormitory_id' => $dormitoryId,
                'code' => 'KMR-UI-P02',
                'name' => 'Kamar UI Putra 02',
                'capacity' => 8,
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $student = StudentRecord::factory()->create([
            'student_no' => 'NIS-ASRAMA-UI',
            'full_name' => 'Santri Asrama UI',
            'primary_unit_id' => $unitId,
            'status' => 'active',
        ]);
        DB::table('student_room_placements')->insert([
            'id' => (string) Str::ulid(),
            'student_id' => $student->id,
            'dormitory_room_id' => $roomId,
            'student_no' => $student->student_no,
            'started_at' => '2026-07-15 08:00:00',
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
            'active_student_key' => $student->id,
            'created_by' => null,
            'ended_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($withDetails) {
            $employee = EmployeeRecord::query()->create([
                'employee_no' => 'PEG-ASRAMA-UI',
                'name' => 'Ustaz Asrama UI',
                'preferred_name' => 'Ustaz UI',
                'employment_type' => 'teacher',
                'primary_unit_id' => $unitId,
                'position' => 'Pembina Asrama',
                'status' => 'active',
                'joined_on' => '2026-07-01',
                'left_on' => null,
                'notes' => null,
            ]);

            DB::table('dormitory_supervisor_assignments')->insert([
                'id' => (string) Str::ulid(),
                'employee_id' => $employee->id,
                'dormitory_id' => $dormitoryId,
                'dormitory_room_id' => null,
                'employee_name' => $employee->name,
                'role' => 'musyrif',
                'started_at' => '2026-07-01 08:00:00',
                'ended_at' => null,
                'status' => 'active',
                'reason' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return [
            'unitId' => $unitId,
            'dormitoryId' => $dormitoryId,
            'roomId' => $roomId,
            'secondRoomId' => $secondRoomId,
        ];
    }

    private function createUser(): User
    {
        $user = User::factory()->create();

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function sourceFile(string $path): string
    {
        $contents = file_get_contents(resource_path($path));

        if ($contents === false) {
            self::fail("File frontend tidak ditemukan: {$path}");
        }

        return $contents;
    }
}
