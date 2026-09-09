<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Database\Seeders;

use App\Models\User;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\HumanResource\HumanResource\Infrastructure\Models\EmployeeRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRevisionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;

final class PrestasiSantriDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $operator = User::where('email', 'operator-santri@example.test')->first();
        $superSystem = User::where('email', 'super-system@example.test')->first();
        $term = AcademicTermRecord::where('code', '2026-2027-GANJIL')->first();
        $activeStudent = StudentRecord::where('student_no', 'NIS-DEMO-AKTIF')->first();
        $acceptedStudent = StudentRecord::where('student_no', 'NIS-DEMO-PPDB')->first();
        $teacher = EmployeeRecord::where('employee_no', 'PEG-DEMO-003')->first();
        $musyrif = EmployeeRecord::where('employee_no', 'PEG-DEMO-002')->first();

        if (! $term || ! $activeStudent || ! $acceptedStudent || ! $teacher) {
            return;
        }

        $academic = $this->upsertCategory(
            code: 'PRS-DEMO-AKD',
            name: 'Akademik',
            description: 'Kategori demo untuk prestasi akademik dan lomba pelajaran.',
            status: 'active',
        );
        $tahfidz = $this->upsertCategory(
            code: 'PRS-DEMO-THF',
            name: 'Tahfidz',
            description: 'Kategori demo untuk penghargaan atau lomba tahfidz.',
            status: 'active',
        );
        $nonAcademic = $this->upsertCategory(
            code: 'PRS-DEMO-NONAKD',
            name: 'Non Akademik',
            description: 'Kategori demo untuk olahraga, seni, bahasa, dan kepemimpinan.',
            status: 'active',
        );
        $archived = $this->upsertCategory(
            code: 'PRS-DEMO-ARSIP',
            name: 'Kategori Lama Diarsipkan',
            description: 'Kategori demo lama yang tidak dipakai untuk input prestasi baru.',
            status: 'archived',
            archivedBy: $superSystem?->id,
            archivedAt: now()->subDays(20),
            archiveReason: 'Kategori demo lama diganti kategori yang lebih sederhana.',
        );

        $draft = $this->upsertAchievement(
            achievementNo: 'PRS-DEMO-DRAFT',
            category: $academic,
            student: $activeStudent,
            term: $term,
            mentor: $teacher,
            title: 'Draft Juara Olimpiade Matematika',
            achievementType: 'competition',
            level: 'regency',
            result: 'Juara 2',
            organizer: 'MGMP Matematika Kabupaten',
            eventName: 'Olimpiade Matematika Santri',
            eventLocation: 'Aula Kabupaten',
            achievedOn: now()->subDays(8)->toDateString(),
            description: 'Contoh draft prestasi yang masih dilengkapi operator.',
            notes: 'Belum diverifikasi.',
            status: 'draft',
            actorId: $operator?->id,
        );
        $this->upsertRevision($draft, 'Draft demo prestasi dibuat.', $operator?->id, null, 'draft', [
            'action' => 'create',
            'changed_fields' => ['category_id', 'student_id', 'academic_period_id', 'mentor_employee_id', 'title', 'level', 'result', 'status'],
            'to_status' => 'draft',
        ]);

        $submitted = $this->upsertAchievement(
            achievementNo: 'PRS-DEMO-SUBMITTED',
            category: $tahfidz,
            student: $acceptedStudent,
            term: $term,
            mentor: $musyrif ?: $teacher,
            title: 'Lomba MHQ Juz 30',
            achievementType: 'award',
            level: 'province',
            result: 'Finalis',
            organizer: 'Forum Tahfidz Provinsi',
            eventName: 'Musabaqah Hifdzil Quran',
            eventLocation: 'Gedung Dakwah Provinsi',
            achievedOn: now()->subDays(7)->toDateString(),
            description: 'Contoh prestasi yang sudah diajukan dan menunggu verifikasi.',
            notes: 'Menunggu pengecekan bukti kegiatan.',
            status: 'submitted',
            actorId: $operator?->id,
            submittedAt: now()->subDays(7)->addHour(),
            submittedBy: $operator?->id,
        );
        $this->upsertRevision($submitted, 'Draft prestasi demo disubmit untuk verifikasi.', $operator?->id, 'draft', 'submitted', [
            'action' => 'submit',
            'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
            'from_status' => 'draft',
            'to_status' => 'submitted',
        ]);

        $verified = $this->upsertAchievement(
            achievementNo: 'PRS-DEMO-VERIFIED',
            category: $academic,
            student: $activeStudent,
            term: $term,
            mentor: $teacher,
            title: 'Juara Olimpiade Sains Pesantren',
            achievementType: 'competition',
            level: 'national',
            result: 'Juara 1',
            organizer: 'Kementerian Agama',
            eventName: 'Olimpiade Sains Pesantren Nasional',
            eventLocation: 'Jakarta',
            achievedOn: now()->subDays(14)->toDateString(),
            description: 'Contoh prestasi resmi yang sudah diverifikasi.',
            notes: 'Masuk riwayat resmi santri.',
            status: 'verified',
            actorId: $operator?->id,
            submittedAt: now()->subDays(14)->addHour(),
            submittedBy: $operator?->id,
            verifiedAt: now()->subDays(13),
            verifiedBy: $superSystem?->id,
            verificationNote: 'Bukti kegiatan dan hasil sudah cocok.',
        );
        $this->upsertRevision($verified, 'Bukti kegiatan dan hasil sudah cocok.', $superSystem?->id, 'submitted', 'verified', [
            'action' => 'verify',
            'changed_fields' => ['status', 'verified_at', 'verified_by', 'verification_note'],
            'from_status' => 'submitted',
            'to_status' => 'verified',
        ]);

        $needsRevision = $this->upsertAchievement(
            achievementNo: 'PRS-DEMO-REVISION',
            category: $nonAcademic,
            student: $acceptedStudent,
            term: $term,
            mentor: $teacher,
            title: 'Festival Pidato Bahasa Arab',
            achievementType: 'competition',
            level: 'province',
            result: 'Peserta Terbaik',
            organizer: 'Forum Bahasa Arab',
            eventName: 'Festival Bahasa Arab Santri',
            eventLocation: 'Kampus Mitra',
            achievedOn: now()->subDays(5)->toDateString(),
            description: 'Contoh prestasi yang perlu revisi sebelum diverifikasi.',
            notes: 'Lengkapi nama penyelenggara resmi pada dokumen.',
            status: 'needs_revision',
            actorId: $operator?->id,
            submittedAt: now()->subDays(5)->addHour(),
            submittedBy: $operator?->id,
            verificationNote: 'Lengkapi dokumen pendukung dan nomor surat kegiatan.',
        );
        $this->upsertRevision($needsRevision, 'Lengkapi dokumen pendukung dan nomor surat kegiatan.', $superSystem?->id, 'submitted', 'needs_revision', [
            'action' => 'request_revision',
            'changed_fields' => ['status', 'verification_note'],
            'from_status' => 'submitted',
            'to_status' => 'needs_revision',
        ]);

        $void = $this->upsertAchievement(
            achievementNo: 'PRS-DEMO-VOID',
            category: $archived,
            student: $acceptedStudent,
            term: $term,
            mentor: $teacher,
            title: 'Catatan Prestasi Salah Input',
            achievementType: 'award',
            level: 'internal',
            result: 'Duplikat',
            organizer: 'Panitia Demo',
            eventName: 'Kegiatan Demo Lama',
            eventLocation: 'Kantor Pesantren',
            achievedOn: now()->subDays(10)->toDateString(),
            description: 'Contoh prestasi yang dibatalkan tanpa menghapus data.',
            notes: 'Catatan ini sengaja disediakan untuk memahami status void.',
            status: 'void',
            actorId: $operator?->id,
            submittedAt: now()->subDays(10)->addHour(),
            submittedBy: $operator?->id,
            voidedAt: now()->subDays(9),
            voidedBy: $superSystem?->id,
            voidReason: 'Dibatalkan karena catatan demo duplikat.',
        );
        $this->upsertRevision($void, 'Dibatalkan karena catatan demo duplikat.', $superSystem?->id, 'submitted', 'void', [
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
        string $status,
        ?string $archivedBy = null,
        mixed $archivedAt = null,
        ?string $archiveReason = null,
    ): StudentAchievementCategoryRecord {
        $category = StudentAchievementCategoryRecord::firstOrNew(['code' => $code]);
        $category->fill([
            'name' => $name,
            'description' => $description,
            'status' => $status,
            'archived_at' => $archivedAt,
            'archived_by' => $archivedBy,
            'archive_reason' => $archiveReason,
        ]);
        $category->save();

        return $category;
    }

    private function upsertAchievement(
        string $achievementNo,
        StudentAchievementCategoryRecord $category,
        StudentRecord $student,
        AcademicTermRecord $term,
        EmployeeRecord $mentor,
        string $title,
        string $achievementType,
        string $level,
        string $result,
        string $organizer,
        string $eventName,
        string $eventLocation,
        string $achievedOn,
        string $description,
        string $notes,
        string $status,
        ?string $actorId,
        mixed $submittedAt = null,
        ?string $submittedBy = null,
        mixed $verifiedAt = null,
        ?string $verifiedBy = null,
        ?string $verificationNote = null,
        mixed $voidedAt = null,
        ?string $voidedBy = null,
        ?string $voidReason = null,
    ): StudentAchievementRecord {
        $achievement = StudentAchievementRecord::firstOrNew(['achievement_no' => $achievementNo]);
        $achievement->fill([
            'category_id' => $category->id,
            'category_name' => $category->name,
            'student_id' => $student->id,
            'student_no' => $student->student_no,
            'student_name' => $student->full_name,
            'academic_period_id' => $term->id,
            'academic_period_label' => $term->name,
            'mentor_employee_id' => $mentor->id,
            'mentor_name' => $mentor->name,
            'title' => $title,
            'achievement_type' => $achievementType,
            'level' => $level,
            'result' => $result,
            'organizer' => $organizer,
            'event_name' => $eventName,
            'event_location' => $eventLocation,
            'achieved_on' => $achievedOn,
            'period_started_on' => null,
            'period_ended_on' => null,
            'description' => $description,
            'notes' => $notes,
            'status' => $status,
            'submitted_at' => $submittedAt,
            'submitted_by' => $submittedBy,
            'verified_at' => $verifiedAt,
            'verified_by' => $verifiedBy,
            'verification_note' => $verificationNote,
            'voided_at' => $voidedAt,
            'voided_by' => $voidedBy,
            'void_reason' => $voidReason,
            'created_by' => $actorId,
        ]);
        $achievement->save();

        return $achievement;
    }

    /** @param array<string, mixed> $summary */
    private function upsertRevision(
        StudentAchievementRecord $achievement,
        string $reason,
        ?string $actorId,
        ?string $fromStatus,
        string $toStatus,
        array $summary,
    ): void {
        $revision = StudentAchievementRevisionRecord::firstOrNew([
            'achievement_id' => $achievement->id,
            'reason' => $reason,
        ]);
        $revision->fill([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $actorId,
            'changed_at' => now()->subHour(),
            'summary' => $summary,
        ]);
        $revision->save();
    }
}
