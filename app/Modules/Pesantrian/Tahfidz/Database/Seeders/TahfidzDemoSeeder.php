<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Database\Seeders;

use App\Models\User;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use Illuminate\Database\Seeder;

final class TahfidzDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $term = AcademicTermRecord::where('code', '2026-2027-GANJIL')->first();
        $activeStudent = StudentRecord::where('student_no', 'NIS-DEMO-AKTIF')->first();
        $acceptedStudent = StudentRecord::where('student_no', 'NIS-DEMO-PPDB')->first();
        $teacher = EmployeeRecord::where('employee_no', 'PEG-DEMO-003')->first();
        $musyrif = EmployeeRecord::where('employee_no', 'PEG-DEMO-002')->first();

        if (! $term || ! $activeStudent || ! $acceptedStudent || ! $teacher) {
            return;
        }

        $regular = $this->upsertProgram(
            code: 'DEMO-THF-REG',
            name: 'Tahfidz Reguler Demo',
            description: 'Program tahfidz reguler untuk santri aktif demo.',
            status: 'active',
            actorId: $actor?->id,
        );
        $intensive = $this->upsertProgram(
            code: 'DEMO-THF-INT',
            name: 'Tahfidz Intensif Demo',
            description: 'Program intensif sebagai contoh program nonaktif.',
            status: 'inactive',
            actorId: $actor?->id,
            archivedAt: now()->subDays(14),
        );

        $activeTarget = $this->upsertTarget(
            program: $regular,
            student: $activeStudent,
            term: $term,
            targetJuz: 1,
            targetSurah: 'Al-Baqarah',
            targetAyahFrom: 1,
            targetAyahTo: 30,
            targetNote: 'Target hafalan awal semester.',
            status: 'active',
            actorId: $actor?->id,
        );
        $acceptedTarget = $this->upsertTarget(
            program: $regular,
            student: $acceptedStudent,
            term: $term,
            targetJuz: 30,
            targetSurah: 'An-Naba',
            targetAyahFrom: 1,
            targetAyahTo: 40,
            targetNote: 'Target juz amma santri baru.',
            status: 'active',
            actorId: $actor?->id,
        );
        $this->upsertTarget(
            program: $intensive,
            student: $activeStudent,
            term: $term,
            targetJuz: 2,
            targetSurah: 'Ali Imran',
            targetAyahFrom: 1,
            targetAyahTo: 20,
            targetNote: 'Contoh target pada program nonaktif.',
            status: 'cancelled',
            actorId: $actor?->id,
        );

        $acceptedSubmission = $this->upsertSubmission(
            program: $regular,
            target: $activeTarget,
            student: $activeStudent,
            supervisor: $teacher,
            submissionDate: now()->subDays(3)->toDateString(),
            type: 'new_memorization',
            juz: 1,
            surah: 'Al-Baqarah',
            ayahFrom: 1,
            ayahTo: 10,
            status: 'accepted',
            qualityNote: 'Setoran demo diterima.',
            actorId: $actor?->id,
            reviewedBy: $actor?->id,
            reviewedAt: now()->subDays(3)->addHour(),
        );
        $this->upsertRevision($acceptedSubmission, 'Setoran diterima oleh pembimbing demo.', $actor?->id, [
            'action' => 'review',
            'from_status' => 'submitted',
            'to_status' => 'accepted',
        ]);

        $this->upsertSubmission(
            program: $regular,
            target: $activeTarget,
            student: $activeStudent,
            supervisor: $teacher,
            submissionDate: now()->subDays(2)->toDateString(),
            type: 'murojaah',
            juz: 1,
            surah: 'Al-Baqarah',
            ayahFrom: 1,
            ayahTo: 10,
            status: 'submitted',
            qualityNote: 'Menunggu review pembimbing.',
            actorId: $actor?->id,
        );

        $this->upsertSubmission(
            program: $regular,
            target: $acceptedTarget,
            student: $acceptedStudent,
            supervisor: $musyrif ?: $teacher,
            submissionDate: now()->subDay()->toDateString(),
            type: 'new_memorization',
            juz: 30,
            surah: 'An-Naba',
            ayahFrom: 1,
            ayahTo: 20,
            status: 'needs_revision',
            qualityNote: 'Perlu pengulangan pada ayat akhir.',
            actorId: $actor?->id,
            reviewedBy: $actor?->id,
            reviewedAt: now()->subDay()->addHour(),
        );

        $voidSubmission = $this->upsertSubmission(
            program: $regular,
            target: $acceptedTarget,
            student: $acceptedStudent,
            supervisor: $teacher,
            submissionDate: now()->subDays(4)->toDateString(),
            type: 'murojaah',
            juz: 30,
            surah: 'An-Naziat',
            ayahFrom: 1,
            ayahTo: 15,
            status: 'void',
            qualityNote: 'Catatan demo dibatalkan.',
            actorId: $actor?->id,
            voidedBy: $actor?->id,
            voidedAt: now()->subDays(4)->addHour(),
            voidReason: 'Setoran demo salah input tanggal.',
        );
        $this->upsertRevision($voidSubmission, 'Setoran dibatalkan karena salah input tanggal.', $actor?->id, [
            'action' => 'void',
            'from_status' => 'draft',
            'to_status' => 'void',
        ]);
    }

    private function upsertProgram(
        string $code,
        string $name,
        string $description,
        string $status,
        ?string $actorId,
        mixed $archivedAt = null,
    ): TahfidzProgramRecord {
        $program = TahfidzProgramRecord::firstOrNew(['code' => $code]);
        $program->fill([
            'name' => $name,
            'description' => $description,
            'status' => $status,
            'created_by' => $actorId,
            'archived_at' => $archivedAt,
            'archived_by' => $archivedAt === null ? null : $actorId,
        ]);
        $program->save();

        return $program;
    }

    private function upsertTarget(
        TahfidzProgramRecord $program,
        StudentRecord $student,
        AcademicTermRecord $term,
        int $targetJuz,
        string $targetSurah,
        int $targetAyahFrom,
        int $targetAyahTo,
        string $targetNote,
        string $status,
        ?string $actorId,
    ): TahfidzTargetRecord {
        $target = TahfidzTargetRecord::firstOrNew([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'academic_period_id' => $term->id,
            'target_juz' => $targetJuz,
        ]);
        $target->fill([
            'student_no' => $student->student_no,
            'student_name' => $student->full_name,
            'period_label' => $term->name,
            'target_surah' => $targetSurah,
            'target_ayah_from' => $targetAyahFrom,
            'target_ayah_to' => $targetAyahTo,
            'target_note' => $targetNote,
            'status' => $status,
            'created_by' => $actorId,
        ]);
        $target->save();

        return $target;
    }

    private function upsertSubmission(
        TahfidzProgramRecord $program,
        TahfidzTargetRecord $target,
        StudentRecord $student,
        EmployeeRecord $supervisor,
        string $submissionDate,
        string $type,
        int $juz,
        string $surah,
        int $ayahFrom,
        int $ayahTo,
        string $status,
        string $qualityNote,
        ?string $actorId,
        ?string $reviewedBy = null,
        mixed $reviewedAt = null,
        ?string $voidedBy = null,
        mixed $voidedAt = null,
        ?string $voidReason = null,
    ): TahfidzSubmissionRecord {
        $submission = TahfidzSubmissionRecord::firstOrNew([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'type' => $type,
            'surah' => $surah,
            'ayah_from' => $ayahFrom,
            'ayah_to' => $ayahTo,
        ]);
        $submission->fill([
            'target_id' => $target->id,
            'student_no' => $student->student_no,
            'student_name' => $student->full_name,
            'supervisor_id' => $supervisor->id,
            'supervisor_name' => $supervisor->name,
            'submission_date' => $submissionDate,
            'juz' => $juz,
            'status' => $status,
            'quality_note' => $qualityNote,
            'created_by' => $actorId,
            'reviewed_at' => $reviewedAt,
            'reviewed_by' => $reviewedBy,
            'voided_at' => $voidedAt,
            'voided_by' => $voidedBy,
            'void_reason' => $voidReason,
        ]);
        $submission->save();

        return $submission;
    }

    /** @param array<string, mixed> $summary */
    private function upsertRevision(TahfidzSubmissionRecord $submission, string $reason, ?string $actorId, array $summary): void
    {
        $revision = TahfidzSubmissionRevisionRecord::firstOrNew([
            'submission_id' => $submission->id,
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
