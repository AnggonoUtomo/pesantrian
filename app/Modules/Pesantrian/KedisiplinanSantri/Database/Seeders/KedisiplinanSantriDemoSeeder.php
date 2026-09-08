<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Database\Seeders;

use App\Models\User;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class KedisiplinanSantriDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $operator = User::where('email', 'operator-santri@example.test')->first();
        $superSystem = User::where('email', 'super-system@example.test')->first();
        $activeStudent = StudentRecord::where('student_no', 'NIS-DEMO-AKTIF')->first();
        $acceptedStudent = StudentRecord::where('student_no', 'NIS-DEMO-PPDB')->first();
        $teacher = EmployeeRecord::where('employee_no', 'PEG-DEMO-003')->first();
        $musyrif = EmployeeRecord::where('employee_no', 'PEG-DEMO-002')->first();

        if (! $activeStudent || ! $acceptedStudent || ! $teacher) {
            return;
        }

        $lateCategory = $this->upsertCategory(
            code: 'DIS-DEMO-TERLAMBAT',
            name: 'Terlambat Kegiatan',
            description: 'Kategori demo untuk keterlambatan kegiatan wajib.',
            severity: 'minor',
            points: 5,
            status: 'active',
        );
        $adabCategory = $this->upsertCategory(
            code: 'DIS-DEMO-ADAB',
            name: 'Adab dan Ketertiban',
            description: 'Kategori demo untuk pelanggaran adab dan ketertiban.',
            severity: 'moderate',
            points: 15,
            status: 'active',
        );
        $seriousCategory = $this->upsertCategory(
            code: 'DIS-DEMO-SERIUS',
            name: 'Pelanggaran Serius',
            description: 'Kategori demo untuk pelanggaran berat yang perlu tindak lanjut.',
            severity: 'major',
            points: 40,
            status: 'active',
        );
        $archivedCategory = $this->upsertCategory(
            code: 'DIS-DEMO-ARSIP',
            name: 'Kategori Lama Diarsipkan',
            description: 'Kategori demo lama yang tidak dipakai untuk kasus baru.',
            severity: 'minor',
            points: 0,
            status: 'archived',
        );

        $draft = $this->upsertCase(
            caseNo: 'DIS-DEMO-DRAFT',
            student: $activeStudent,
            category: $lateCategory,
            severity: 'minor',
            points: 5,
            occurredAt: now()->subDays(2)->setTime(7, 15)->toDateTimeString(),
            location: 'Masjid',
            description: 'Draft catatan santri terlambat hadir kegiatan pagi.',
            status: 'draft',
            actorId: $operator?->id,
        );
        $this->upsertRevision($draft, 'Draft demo kasus kedisiplinan dibuat.', $operator?->id, [
            'action' => 'create',
            'changed_fields' => ['student_id', 'category_id', 'severity', 'points', 'occurred_at', 'location', 'description', 'status'],
            'to_status' => 'draft',
        ]);

        $submitted = $this->upsertCase(
            caseNo: 'DIS-DEMO-SUBMITTED',
            student: $acceptedStudent,
            category: $adabCategory,
            severity: 'moderate',
            points: 15,
            occurredAt: now()->subDay()->setTime(20, 0)->toDateTimeString(),
            location: 'Asrama Putra',
            description: 'Kasus demo sudah disubmit dan menunggu review pembina.',
            status: 'submitted',
            actorId: $operator?->id,
            submittedAt: now()->subDay()->setTime(21, 0)->toDateTimeString(),
        );
        $this->upsertRevision($submitted, 'Draft kasus demo disubmit untuk review.', $operator?->id, [
            'action' => 'submit',
            'changed_fields' => ['status', 'submitted_at'],
            'from_status' => 'draft',
            'to_status' => 'submitted',
        ]);

        $inReview = $this->upsertCase(
            caseNo: 'DIS-DEMO-INREVIEW',
            student: $activeStudent,
            category: $seriousCategory,
            severity: 'major',
            points: 40,
            occurredAt: now()->subDays(3)->setTime(13, 30)->toDateTimeString(),
            location: 'Halaman Pesantren',
            description: 'Kasus demo sedang dalam review pembina.',
            status: 'in_review',
            actorId: $operator?->id,
            submittedAt: now()->subDays(3)->setTime(14, 0)->toDateTimeString(),
            reviewedAt: now()->subDays(3)->setTime(15, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Kronologi demo sudah diklarifikasi dengan musyrif.',
        );
        $this->upsertRevision($inReview, 'Kronologi demo sudah diklarifikasi dengan musyrif.', $superSystem?->id, [
            'action' => 'review',
            'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
            'from_status' => 'submitted',
            'to_status' => 'in_review',
        ]);

        $actionAssigned = $this->upsertCase(
            caseNo: 'DIS-DEMO-ACTION',
            student: $acceptedStudent,
            category: $adabCategory,
            severity: 'moderate',
            points: 15,
            occurredAt: now()->subDays(4)->setTime(6, 45)->toDateTimeString(),
            location: 'Kelas',
            description: 'Kasus demo sudah punya rencana tindakan pembinaan.',
            status: 'action_assigned',
            actorId: $operator?->id,
            assignedEmployee: $musyrif ?: $teacher,
            submittedAt: now()->subDays(4)->setTime(7, 30)->toDateTimeString(),
            reviewedAt: now()->subDays(4)->setTime(8, 30)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Butuh pembinaan kedisiplinan ringan.',
            actionPlan: 'Refleksi tertulis dan pendampingan adab bersama musyrif.',
            actionAssignedAt: now()->subDays(4)->setTime(9, 0)->toDateTimeString(),
        );
        $this->upsertRevision($actionAssigned, 'Refleksi tertulis dan pendampingan adab bersama musyrif.', $superSystem?->id, [
            'action' => 'assign_action',
            'changed_fields' => ['status', 'action_plan', 'action_assigned_at', 'assigned_employee_id'],
            'from_status' => 'in_review',
            'to_status' => 'action_assigned',
        ]);

        $resolved = $this->upsertCase(
            caseNo: 'DIS-DEMO-RESOLVED',
            student: $activeStudent,
            category: $lateCategory,
            severity: 'minor',
            points: 5,
            occurredAt: now()->subDays(5)->setTime(7, 10)->toDateTimeString(),
            location: 'Masjid',
            description: 'Kasus demo sudah selesai setelah tindakan pembinaan.',
            status: 'resolved',
            actorId: $operator?->id,
            assignedEmployee: $teacher,
            submittedAt: now()->subDays(5)->setTime(8, 0)->toDateTimeString(),
            reviewedAt: now()->subDays(5)->setTime(9, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Kasus terlambat berulang dan perlu tindak lanjut.',
            actionPlan: 'Piket kebersihan masjid selama tiga hari.',
            actionAssignedAt: now()->subDays(5)->setTime(10, 0)->toDateTimeString(),
            resolvedAt: now()->subDays(2)->setTime(16, 0)->toDateTimeString(),
            resolvedBy: $operator?->id,
            resolutionNote: 'Santri menyelesaikan piket pembinaan dan membuat refleksi.',
        );
        $this->upsertRevision($resolved, 'Santri menyelesaikan piket pembinaan dan membuat refleksi.', $operator?->id, [
            'action' => 'resolve',
            'changed_fields' => ['status', 'resolved_at', 'resolved_by', 'resolution_note'],
            'from_status' => 'action_assigned',
            'to_status' => 'resolved',
        ]);

        $void = $this->upsertCase(
            caseNo: 'DIS-DEMO-VOID',
            student: $acceptedStudent,
            category: $archivedCategory,
            severity: 'minor',
            points: 0,
            occurredAt: now()->subDays(6)->setTime(12, 0)->toDateTimeString(),
            location: 'Kantor Pengasuhan',
            description: 'Kasus demo salah input dan dibatalkan tanpa menghapus data.',
            status: 'void',
            actorId: $operator?->id,
            submittedAt: now()->subDays(6)->setTime(13, 0)->toDateTimeString(),
            voidedAt: now()->subDays(6)->setTime(14, 0)->toDateTimeString(),
            voidedBy: $superSystem?->id,
            voidReason: 'Dibatalkan karena laporan demo duplikat.',
        );
        $this->upsertRevision($void, 'Dibatalkan karena laporan demo duplikat.', $superSystem?->id, [
            'action' => 'void',
            'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
            'from_status' => 'submitted',
            'to_status' => 'void',
        ]);
    }

    private function upsertCategory(
        string $code,
        string $name,
        string $description,
        string $severity,
        int $points,
        string $status,
    ): StudentDisciplineCategoryRecord {
        $category = StudentDisciplineCategoryRecord::firstOrNew(['code' => $code]);
        $category->fill([
            'name' => $name,
            'description' => $description,
            'default_severity' => $severity,
            'default_points' => $points,
            'status' => $status,
        ]);
        $category->save();

        return $category;
    }

    private function upsertCase(
        string $caseNo,
        StudentRecord $student,
        StudentDisciplineCategoryRecord $category,
        string $severity,
        int $points,
        string $occurredAt,
        string $location,
        string $description,
        string $status,
        ?string $actorId,
        ?EmployeeRecord $assignedEmployee = null,
        ?string $submittedAt = null,
        ?string $reviewedAt = null,
        ?string $reviewedBy = null,
        ?string $reviewNote = null,
        ?string $actionPlan = null,
        ?string $actionAssignedAt = null,
        ?string $resolvedAt = null,
        ?string $resolvedBy = null,
        ?string $resolutionNote = null,
        ?string $voidedAt = null,
        ?string $voidedBy = null,
        ?string $voidReason = null,
    ): StudentDisciplineCaseRecord {
        $case = StudentDisciplineCaseRecord::firstOrNew(['case_no' => $caseNo]);
        $case->fill([
            'student_id' => $student->id,
            'student_no' => $student->student_no,
            'student_name' => $student->full_name,
            'unit_id' => $student->primary_unit_id,
            'unit_name' => $this->unitName($student->primary_unit_id),
            'category_id' => $category->id,
            'category_name' => $category->name,
            'severity' => $severity,
            'points' => $points,
            'occurred_at' => $occurredAt,
            'location' => $location,
            'description' => $description,
            'reported_by' => $actorId,
            'assigned_employee_id' => $assignedEmployee?->id,
            'assigned_employee_name' => $assignedEmployee?->name,
            'status' => $status,
            'submitted_at' => $submittedAt,
            'reviewed_at' => $reviewedAt,
            'reviewed_by' => $reviewedBy,
            'review_note' => $reviewNote,
            'action_plan' => $actionPlan,
            'action_assigned_at' => $actionAssignedAt,
            'resolved_at' => $resolvedAt,
            'resolved_by' => $resolvedBy,
            'resolution_note' => $resolutionNote,
            'voided_at' => $voidedAt,
            'voided_by' => $voidedBy,
            'void_reason' => $voidReason,
            'created_by' => $actorId,
        ]);
        $case->save();

        return $case;
    }

    private function unitName(?string $unitId): ?string
    {
        if ($unitId === null) {
            return null;
        }

        $name = DB::table('organization_units')
            ->where('id', $unitId)
            ->value('name');

        return is_string($name) ? $name : null;
    }

    /** @param array<string, mixed> $summary */
    private function upsertRevision(StudentDisciplineCaseRecord $case, string $reason, ?string $actorId, array $summary): void
    {
        $revision = StudentDisciplineRevisionRecord::firstOrNew([
            'case_id' => $case->id,
            'reason' => $reason,
        ]);
        $revision->fill([
            'changed_by' => $actorId,
            'changed_at' => now()->subHour(),
            'summary' => $summary,
        ]);
        $revision->save();
    }
}
