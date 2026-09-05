<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Database\Seeders;

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitorySupervisorAssignmentRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\StudentRoomPlacementRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;

final class AsramaDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $asramaPutra = OrganizationUnitRecord::where('code', 'DEMO-ASRAMA-PUTRA')->first();
        $asramaPutri = OrganizationUnitRecord::where('code', 'DEMO-ASRAMA-PUTRI')->first();

        if (! $asramaPutra || ! $asramaPutri) {
            return;
        }

        $putra = $this->upsertDormitory($asramaPutra->id, 'DEMO-ASR-PUTRA', 'Asrama Putra Demo', 'male', 'active', null, null);
        $putri = $this->upsertDormitory($asramaPutri->id, 'DEMO-ASR-PUTRI', 'Asrama Putri Demo', 'female', 'active', null, null);
        $renovasi = $this->upsertDormitory($asramaPutra->id, 'DEMO-ASR-RENOVASI', 'Asrama Renovasi Demo', 'male', 'inactive', now()->subMonth(), $actor?->id);

        $putraA = $this->upsertRoom($putra, 'DEMO-KMR-PUTRA-A01', 'Kamar Putra A-01', 8, 'active', null, null);
        $putraB = $this->upsertRoom($putra, 'DEMO-KMR-PUTRA-A02', 'Kamar Putra A-02', 8, 'active', null, null);
        $putriA = $this->upsertRoom($putri, 'DEMO-KMR-PUTRI-A01', 'Kamar Putri A-01', 8, 'active', null, null);
        $putriB = $this->upsertRoom($putri, 'DEMO-KMR-PUTRI-A02', 'Kamar Putri A-02', 8, 'active', null, null);
        $this->upsertRoom($renovasi, 'DEMO-KMR-RENOVASI-A01', 'Kamar Renovasi A-01', 6, 'inactive', now()->subMonth(), $actor?->id);

        $this->upsertPlacement($putraA, 'NIS-DEMO-AKTIF', '2026-07-15 08:00:00', null, 'active', null, $actor?->id, null);
        $this->upsertPlacement($putraA, 'NIS-DEMO-PPDB', '2026-07-15 08:00:00', '2026-08-01 08:00:00', 'moved', 'Pindah kamar demo historis.', $actor?->id, $actor?->id);
        $this->upsertPlacement($putraB, 'NIS-DEMO-PPDB', '2026-08-01 08:00:00', null, 'active', null, $actor?->id, null);
        $this->upsertPlacement($putriA, 'NIS-DEMO-NONAKTIF', '2026-07-15 08:00:00', '2026-08-15 17:00:00', 'inactive', 'Keluar kamar demo historis.', $actor?->id, $actor?->id);

        $this->upsertSupervisor($putri, $putriB, 'PEG-DEMO-002', 'musyrif', '2026-07-01 08:00:00', null, 'active', null);
        $this->upsertSupervisor($putra, null, 'PEG-DEMO-006', 'pembina', '2025-07-01 08:00:00', '2026-06-30 17:00:00', 'ended', 'Selesai tugas pembinaan demo.');
    }

    private function upsertDormitory(
        string $unitId,
        string $code,
        string $name,
        string $genderPolicy,
        string $status,
        mixed $archivedAt,
        ?string $archivedBy,
    ): DormitoryRecord {
        $dormitory = DormitoryRecord::firstOrNew(['code' => $code]);
        $dormitory->fill([
            'unit_id' => $unitId,
            'name' => $name,
            'gender_policy' => $genderPolicy,
            'description' => 'Data demo Asrama untuk uji lifecycle kamar, placement, dan musyrif.',
            'status' => $status,
            'archived_at' => $archivedAt,
            'archived_by' => $archivedBy,
        ]);
        $dormitory->save();

        return $dormitory;
    }

    private function upsertRoom(
        DormitoryRecord $dormitory,
        string $code,
        string $name,
        int $capacity,
        string $status,
        mixed $archivedAt,
        ?string $archivedBy,
    ): DormitoryRoomRecord {
        $room = DormitoryRoomRecord::firstOrNew([
            'dormitory_id' => $dormitory->id,
            'code' => $code,
        ]);
        $room->fill([
            'name' => $name,
            'capacity' => $capacity,
            'status' => $status,
            'archived_at' => $archivedAt,
            'archived_by' => $archivedBy,
        ]);
        $room->save();

        return $room;
    }

    private function upsertPlacement(
        DormitoryRoomRecord $room,
        string $studentNo,
        string $startedAt,
        ?string $endedAt,
        string $status,
        ?string $reason,
        ?string $createdBy,
        ?string $endedBy,
    ): void {
        $student = StudentRecord::where('student_no', $studentNo)->first();

        if (! $student instanceof StudentRecord) {
            return;
        }

        $placement = StudentRoomPlacementRecord::firstOrNew([
            'student_id' => $student->id,
            'dormitory_room_id' => $room->id,
            'status' => $status,
        ]);
        $placement->fill([
            'student_no' => $student->student_no,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'reason' => $reason,
            'active_student_key' => $status === 'active' ? $student->id : null,
            'created_by' => $createdBy,
            'ended_by' => $endedBy,
        ]);
        $placement->save();
    }

    private function upsertSupervisor(
        DormitoryRecord $dormitory,
        ?DormitoryRoomRecord $room,
        string $employeeNo,
        string $role,
        string $startedAt,
        ?string $endedAt,
        string $status,
        ?string $reason,
    ): void {
        $employee = EmployeeRecord::where('employee_no', $employeeNo)->first();

        if (! $employee instanceof EmployeeRecord) {
            return;
        }

        $assignment = DormitorySupervisorAssignmentRecord::firstOrNew([
            'employee_id' => $employee->id,
            'dormitory_id' => $dormitory->id,
            'dormitory_room_id' => $room?->id,
            'status' => $status,
        ]);
        $assignment->fill([
            'employee_name' => $employee->name,
            'role' => $role,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'reason' => $reason,
        ]);
        $assignment->save();
    }
}
