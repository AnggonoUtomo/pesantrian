<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Database\Seeders;

use App\Models\User;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;

final class PerizinanSantriDemoSeeder extends Seeder
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

        if (! $activeStudent || ! $acceptedStudent) {
            return;
        }

        $today = now()->startOfDay();

        $draft = $this->upsertPermit(
            permitNo: 'IZN-DEMO-DRAFT',
            student: $activeStudent,
            permitType: 'home_visit',
            startsAt: $today->copy()->addDays(10)->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDays(10)->setTime(17, 0)->toDateTimeString(),
            destination: 'Rumah wali demo',
            reason: 'Draft izin pulang singkat yang belum disubmit.',
            status: 'draft',
            actorId: $operator?->id,
        );

        $submitted = $this->upsertPermit(
            permitNo: 'IZN-DEMO-SUBMITTED',
            student: $acceptedStudent,
            permitType: 'activity',
            startsAt: $today->copy()->addDays(11)->setTime(13, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDays(11)->setTime(17, 0)->toDateTimeString(),
            destination: 'Kantor kecamatan demo',
            reason: 'Mengikuti kegiatan lomba tingkat kecamatan.',
            status: 'submitted',
            actorId: $operator?->id,
            submittedAt: $today->copy()->addDays(9)->setTime(9, 0)->toDateTimeString(),
            submittedBy: $operator?->id,
        );
        $this->upsertRevision($submitted, 'Permohonan izin disubmit untuk review.', $operator?->id, [
            'action' => 'submit',
            'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
            'to_status' => 'submitted',
        ]);

        $approved = $this->upsertPermit(
            permitNo: 'IZN-DEMO-APPROVED',
            student: $activeStudent,
            permitType: 'leave',
            startsAt: $today->copy()->addDays(12)->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDays(12)->setTime(16, 0)->toDateTimeString(),
            destination: 'Klinik luar pesantren',
            reason: 'Kontrol kesehatan rutin bersama wali.',
            status: 'approved',
            actorId: $operator?->id,
            submittedAt: $today->copy()->addDays(10)->setTime(8, 30)->toDateTimeString(),
            submittedBy: $operator?->id,
            reviewedAt: $today->copy()->addDays(10)->setTime(10, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Disetujui untuk kontrol terjadwal.',
        );
        $this->upsertRevision($approved, 'Disetujui untuk kontrol terjadwal.', $superSystem?->id, [
            'action' => 'review',
            'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
            'from_status' => 'submitted',
            'to_status' => 'approved',
        ]);

        $rejected = $this->upsertPermit(
            permitNo: 'IZN-DEMO-REJECTED',
            student: $acceptedStudent,
            permitType: 'home_visit',
            startsAt: $today->copy()->addDays(13)->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDays(13)->setTime(17, 0)->toDateTimeString(),
            destination: 'Rumah wali demo',
            reason: 'Permohonan pulang tanpa konfirmasi wali.',
            status: 'rejected',
            actorId: $operator?->id,
            submittedAt: $today->copy()->addDays(11)->setTime(8, 0)->toDateTimeString(),
            submittedBy: $operator?->id,
            reviewedAt: $today->copy()->addDays(11)->setTime(9, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Ditolak karena wali belum terkonfirmasi.',
        );
        $this->upsertRevision($rejected, 'Ditolak karena wali belum terkonfirmasi.', $superSystem?->id, [
            'action' => 'review',
            'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
            'from_status' => 'submitted',
            'to_status' => 'rejected',
        ]);

        $checkedOut = $this->upsertPermit(
            permitNo: 'IZN-DEMO-CHECKEDOUT',
            student: $activeStudent,
            permitType: 'home_visit',
            startsAt: $today->copy()->addDay()->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDay()->setTime(17, 0)->toDateTimeString(),
            destination: 'Rumah wali demo',
            reason: 'Izin pulang yang sedang berjalan.',
            status: 'checked_out',
            actorId: $operator?->id,
            submittedAt: $today->copy()->subDay()->setTime(8, 0)->toDateTimeString(),
            submittedBy: $operator?->id,
            reviewedAt: $today->copy()->subDay()->setTime(9, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Disetujui dan sudah keluar.',
            checkedOutAt: $today->copy()->addDay()->setTime(8, 15)->toDateTimeString(),
            checkedOutBy: $operator?->id,
        );
        $this->upsertRevision($checkedOut, 'Santri dicatat keluar sesuai izin yang disetujui.', $operator?->id, [
            'action' => 'checkout',
            'changed_fields' => ['status', 'checked_out_at', 'checked_out_by'],
            'from_status' => 'approved',
            'to_status' => 'checked_out',
        ]);

        $returned = $this->upsertPermit(
            permitNo: 'IZN-DEMO-RETURNED',
            student: $acceptedStudent,
            permitType: 'activity',
            startsAt: $today->copy()->subDays(2)->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->subDays(2)->setTime(17, 0)->toDateTimeString(),
            destination: 'Kegiatan dakwah demo',
            reason: 'Mengikuti kegiatan luar pesantren.',
            status: 'returned',
            actorId: $operator?->id,
            submittedAt: $today->copy()->subDays(4)->setTime(8, 0)->toDateTimeString(),
            submittedBy: $operator?->id,
            reviewedAt: $today->copy()->subDays(4)->setTime(9, 0)->toDateTimeString(),
            reviewedBy: $superSystem?->id,
            reviewNote: 'Disetujui untuk kegiatan luar.',
            checkedOutAt: $today->copy()->subDays(2)->setTime(8, 10)->toDateTimeString(),
            checkedOutBy: $operator?->id,
            returnedAt: $today->copy()->subDays(2)->setTime(18, 30)->toDateTimeString(),
            returnedBy: $operator?->id,
            returnNote: 'Kembali terlambat karena kendaraan rombongan tertunda.',
        );
        $this->upsertRevision($returned, 'Kembali terlambat karena kendaraan rombongan tertunda.', $operator?->id, [
            'action' => 'return',
            'changed_fields' => ['status', 'returned_at', 'returned_by', 'return_note'],
            'from_status' => 'checked_out',
            'to_status' => 'returned',
            'is_late' => true,
        ]);

        $void = $this->upsertPermit(
            permitNo: 'IZN-DEMO-VOID',
            student: $activeStudent,
            permitType: 'sick',
            startsAt: $today->copy()->addDays(14)->setTime(8, 0)->toDateTimeString(),
            endsAt: $today->copy()->addDays(14)->setTime(11, 0)->toDateTimeString(),
            destination: 'Klinik demo',
            reason: 'Contoh izin salah input.',
            status: 'void',
            actorId: $operator?->id,
            submittedAt: $today->copy()->addDays(12)->setTime(8, 0)->toDateTimeString(),
            submittedBy: $operator?->id,
            voidedAt: $today->copy()->addDays(12)->setTime(9, 0)->toDateTimeString(),
            voidedBy: $superSystem?->id,
            voidReason: 'Dibatalkan karena data tanggal salah input.',
        );
        $this->upsertRevision($void, 'Dibatalkan karena data tanggal salah input.', $superSystem?->id, [
            'action' => 'void',
            'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
            'from_status' => 'submitted',
            'to_status' => 'void',
        ]);

        $this->upsertRevision($draft, 'Draft demo dibuat untuk latihan input izin.', $operator?->id, [
            'action' => 'create_draft',
            'changed_fields' => ['permit_no', 'student_id', 'permit_type', 'starts_at', 'ends_at', 'reason'],
            'to_status' => 'draft',
        ]);
    }

    private function upsertPermit(
        string $permitNo,
        StudentRecord $student,
        string $permitType,
        string $startsAt,
        string $endsAt,
        string $destination,
        string $reason,
        string $status,
        ?string $actorId,
        ?string $submittedAt = null,
        ?string $submittedBy = null,
        ?string $reviewedAt = null,
        ?string $reviewedBy = null,
        ?string $reviewNote = null,
        ?string $checkedOutAt = null,
        ?string $checkedOutBy = null,
        ?string $returnedAt = null,
        ?string $returnedBy = null,
        ?string $returnNote = null,
        ?string $voidedAt = null,
        ?string $voidedBy = null,
        ?string $voidReason = null,
    ): StudentPermitRecord {
        $guardian = StudentGuardianRecord::where('student_id', $student->id)
            ->where('is_primary', true)
            ->first();
        $permit = StudentPermitRecord::firstOrNew(['permit_no' => $permitNo]);
        $permit->fill([
            'student_id' => $student->id,
            'student_no' => $student->student_no,
            'student_name' => $student->full_name,
            'permit_type' => $permitType,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'destination' => $destination,
            'reason' => $reason,
            'guardian_name' => $guardian?->guardian_name,
            'guardian_phone' => $guardian?->guardian_phone,
            'guardian_relation' => $guardian?->guardian_relation,
            'status' => $status,
            'submitted_at' => $submittedAt,
            'submitted_by' => $submittedBy,
            'reviewed_at' => $reviewedAt,
            'reviewed_by' => $reviewedBy,
            'review_note' => $reviewNote,
            'checked_out_at' => $checkedOutAt,
            'checked_out_by' => $checkedOutBy,
            'returned_at' => $returnedAt,
            'returned_by' => $returnedBy,
            'return_note' => $returnNote,
            'voided_at' => $voidedAt,
            'voided_by' => $voidedBy,
            'void_reason' => $voidReason,
            'created_by' => $actorId,
        ]);
        $permit->save();

        return $permit;
    }

    /** @param array<string, mixed> $summary */
    private function upsertRevision(StudentPermitRecord $permit, string $reason, ?string $actorId, array $summary): void
    {
        $revision = StudentPermitRevisionRecord::firstOrNew([
            'permit_id' => $permit->id,
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
