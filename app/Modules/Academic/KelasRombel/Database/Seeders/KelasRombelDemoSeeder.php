<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Database\Seeders;

use App\Models\User;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupHomeroomRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupStudentRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;

final class KelasRombelDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $mts = OrganizationUnitRecord::where('code', 'DEMO-MTS')->first();
        $ma = OrganizationUnitRecord::where('code', 'DEMO-MA')->first();
        $activeYear = AcademicYearRecord::where('code', '2026-2027')->first();
        $activeTerm = AcademicTermRecord::where('code', '2026-2027-GANJIL')->first();
        $closedTerm = AcademicTermRecord::where('code', '2025-2026-GENAP')->first();

        if (! $mts || ! $ma || ! $activeYear || ! $activeTerm || ! $closedTerm) {
            return;
        }

        $merdeka = $this->upsertCurriculum('KUR-DEMO-MERDEKA', 'Kurikulum Merdeka Pesantren', 'active');
        $diniyah = $this->upsertCurriculum('KUR-DEMO-DINIYAH', 'Kurikulum Diniyah Terpadu', 'active');
        $arsip = $this->upsertCurriculum('KUR-DEMO-ARSIP', 'Kurikulum Lama Demo', 'archived');

        $mtsVii = $this->upsertLevel($mts->id, 'DEMO-VII', 'Kelas VII Demo', 7, 'active');
        $mtsViii = $this->upsertLevel($mts->id, 'DEMO-VIII', 'Kelas VIII Demo', 8, 'active');
        $maX = $this->upsertLevel($ma->id, 'DEMO-X', 'Kelas X Demo', 10, 'active');
        $maXi = $this->upsertLevel($ma->id, 'DEMO-XI', 'Kelas XI Demo', 11, 'draft');

        $mtsViiA = $this->upsertClassGroup($activeYear->id, $activeTerm->id, $mts->id, $merdeka->id, $mtsVii->id, 'DEMO-MTS-VII-A', 'VII A Demo', 'active', 32);
        $mtsViiB = $this->upsertClassGroup($activeYear->id, $activeTerm->id, $mts->id, $merdeka->id, $mtsVii->id, 'DEMO-MTS-VII-B', 'VII B Demo', 'draft', 30);
        $maXA = $this->upsertClassGroup($activeYear->id, $activeTerm->id, $ma->id, $diniyah->id, $maX->id, 'DEMO-MA-X-A', 'X A Demo', 'active', 28);
        $this->upsertClassGroup($activeYear->id, $activeTerm->id, $ma->id, $diniyah->id, $maXi->id, 'DEMO-MA-XI-A', 'XI A Demo', 'closed', 26);
        $this->upsertClassGroup($activeYear->id, $activeTerm->id, $mts->id, $arsip->id, $mtsViii->id, 'DEMO-ARSIP', 'Rombel Arsip Demo', 'archived', 24, $actor?->id);

        $this->upsertPlacement($mtsViiA, 'NIS-DEMO-AKTIF', '2026-07-15', null, 'active', null);
        $this->upsertPlacement($maXA, 'NIS-DEMO-PPDB', '2026-07-15', null, 'active', null);
        $this->upsertPlacement($mtsViiA, 'NIS-DEMO-PINDAH', '2026-07-15', '2026-08-01', 'transferred', 'Pindah rombel demo historis.');
        $this->upsertPlacement($mtsViiB, 'NIS-DEMO-ARSIP', '2026-07-15', '2026-08-15', 'removed', 'Keluar rombel demo historis.');

        $this->upsertHomeroom($mtsViiA, 'PEG-DEMO-003', '2026-07-01', null, 'active', null);
        $this->upsertHomeroom($maXA, 'PEG-DEMO-004', '2026-07-01', null, 'active', null);
        $this->upsertHomeroom($mtsViiB, 'PEG-DEMO-003', '2025-07-01', '2026-06-30', 'ended', 'Wali kelas selesai pada periode demo sebelumnya.');
    }

    private function upsertCurriculum(string $code, string $name, string $status): AcademicCurriculumRecord
    {
        $curriculum = AcademicCurriculumRecord::firstOrNew(['code' => $code]);
        $curriculum->fill([
            'name' => $name,
            'description' => 'Data demo Kelas / Rombel / Kurikulum.',
            'status' => $status,
        ]);
        $curriculum->save();

        return $curriculum;
    }

    private function upsertLevel(string $unitId, string $code, string $name, int $sequence, string $status): ClassLevelRecord
    {
        $level = ClassLevelRecord::firstOrNew([
            'unit_id' => $unitId,
            'code' => $code,
        ]);
        $level->fill([
            'name' => $name,
            'sequence' => $sequence,
            'status' => $status,
        ]);
        $level->save();

        return $level;
    }

    private function upsertClassGroup(
        string $academicYearId,
        string $academicTermId,
        string $unitId,
        ?string $curriculumId,
        string $classLevelId,
        string $code,
        string $name,
        string $status,
        int $capacity,
        ?string $archivedBy = null,
    ): ClassGroupRecord {
        $classGroup = ClassGroupRecord::firstOrNew([
            'unit_id' => $unitId,
            'academic_year_id' => $academicYearId,
            'academic_term_id' => $academicTermId,
            'code' => $code,
        ]);
        $classGroup->fill([
            'curriculum_id' => $curriculumId,
            'class_level_id' => $classLevelId,
            'name' => $name,
            'capacity' => $capacity,
            'status' => $status,
            'archived_at' => $status === 'archived' ? now()->subMonth() : null,
            'archived_by' => $status === 'archived' ? $archivedBy : null,
        ]);
        $classGroup->save();

        return $classGroup;
    }

    private function upsertPlacement(
        ClassGroupRecord $classGroup,
        string $studentNo,
        string $joinedOn,
        ?string $leftOn,
        string $status,
        ?string $reason,
    ): void {
        $student = StudentRecord::where('student_no', $studentNo)->first();

        if (! $student instanceof StudentRecord) {
            return;
        }

        $placement = ClassGroupStudentRecord::firstOrNew([
            'class_group_id' => $classGroup->id,
            'student_id' => $student->id,
            'status' => $status,
        ]);
        $placement->fill([
            'academic_term_id' => $classGroup->academic_term_id,
            'student_no' => $student->student_no,
            'joined_on' => $joinedOn,
            'left_on' => $leftOn,
            'reason' => $reason,
            'active_period_student_key' => $status === 'active'
                ? $classGroup->academic_term_id.':'.$student->id
                : null,
        ]);
        $placement->save();
    }

    private function upsertHomeroom(
        ClassGroupRecord $classGroup,
        string $employeeNo,
        string $assignedOn,
        ?string $endedOn,
        string $status,
        ?string $reason,
    ): void {
        $employee = EmployeeRecord::where('employee_no', $employeeNo)->first();

        if (! $employee instanceof EmployeeRecord) {
            return;
        }

        $homeroom = ClassGroupHomeroomRecord::firstOrNew([
            'class_group_id' => $classGroup->id,
            'employee_id' => $employee->id,
            'status' => $status,
        ]);
        $homeroom->fill([
            'employee_name' => $employee->name,
            'assigned_on' => $assignedOn,
            'ended_on' => $endedOn,
            'reason' => $reason,
            'active_class_group_key' => $status === 'active' ? $classGroup->id : null,
        ]);
        $homeroom->save();
    }
}
