<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Database\Seeders;

use App\Models\User;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceRevisionRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;

final class PresensiSantriDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $classGroup = ClassGroupRecord::where('code', 'DEMO-MTS-VII-A')->first();
        $dormitory = DormitoryRecord::where('code', 'DEMO-ASR-PUTRA')->first();
        $activeStudent = StudentRecord::where('student_no', 'NIS-DEMO-AKTIF')->first();
        $acceptedStudent = StudentRecord::where('student_no', 'NIS-DEMO-PPDB')->first();

        if (! $classGroup || ! $dormitory || ! $activeStudent || ! $acceptedStudent) {
            return;
        }

        $today = now()->toDateString();

        $classSession = $this->upsertSession(
            attendanceDate: $today,
            contextType: 'class_group',
            contextId: $classGroup->id,
            contextName: $classGroup->name,
            sessionCode: 'DEMO-KBM-PAGI',
            sessionName: 'KBM Pagi Demo',
            status: 'submitted',
            actorId: $actor?->id,
        );
        $this->upsertEntry($classSession, $activeStudent, 'present');
        $this->upsertEntry($classSession, $acceptedStudent, 'late', 15, 'Terlambat apel pagi.');

        $dormitorySession = $this->upsertSession(
            attendanceDate: $today,
            contextType: 'dormitory',
            contextId: $dormitory->id,
            contextName: $dormitory->name,
            sessionCode: 'DEMO-ASRAMA-MALAM',
            sessionName: 'Presensi Asrama Malam Demo',
            status: 'draft',
            actorId: $actor?->id,
        );
        $this->upsertEntry($dormitorySession, $activeStudent, 'excused', null, 'Izin kegiatan keluarga.');
        $this->upsertEntry($dormitorySession, $acceptedStudent, 'sick', null, 'Sakit ringan di kamar.');

        $activitySession = $this->upsertSession(
            attendanceDate: now()->subDay()->toDateString(),
            contextType: 'activity',
            contextId: null,
            contextName: 'Kegiatan Umum Demo',
            sessionCode: 'DEMO-MUHADHARAH',
            sessionName: 'Muhadharah Demo',
            status: 'revised',
            actorId: $actor?->id,
        );
        $this->upsertEntry($activitySession, $activeStudent, 'absent', null, 'Alfa pada catatan awal.');
        $this->upsertEntry($activitySession, $acceptedStudent, 'present');
        $this->upsertRevision($activitySession, 'Koreksi status setelah konfirmasi pembina.', $actor?->id);

        $voidSession = $this->upsertSession(
            attendanceDate: now()->subDays(2)->toDateString(),
            contextType: 'activity',
            contextId: null,
            contextName: 'Kegiatan Salah Input Demo',
            sessionCode: 'DEMO-VOID',
            sessionName: 'Sesi Dibatalkan Demo',
            status: 'void',
            actorId: $actor?->id,
            voidReason: 'Sesi demo dibatalkan karena salah tanggal.',
        );
        $this->upsertEntry($voidSession, $activeStudent, 'present');
    }

    private function upsertSession(
        string $attendanceDate,
        string $contextType,
        ?string $contextId,
        string $contextName,
        string $sessionCode,
        string $sessionName,
        string $status,
        ?string $actorId,
        ?string $voidReason = null,
    ): StudentAttendanceSessionRecord {
        $session = StudentAttendanceSessionRecord::firstOrNew(['session_code' => $sessionCode]);
        $session->fill([
            'attendance_date' => $attendanceDate,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'context_name' => $contextName,
            'session_name' => $sessionName,
            'status' => $status,
            'submitted_at' => in_array($status, ['submitted', 'revised'], true) ? now()->subHours(2) : null,
            'submitted_by' => in_array($status, ['submitted', 'revised'], true) ? $actorId : null,
            'voided_at' => $status === 'void' ? now()->subDay() : null,
            'voided_by' => $status === 'void' ? $actorId : null,
            'void_reason' => $status === 'void' ? $voidReason : null,
            'created_by' => $actorId,
        ]);
        $session->save();

        return $session;
    }

    private function upsertEntry(
        StudentAttendanceSessionRecord $session,
        StudentRecord $student,
        string $status,
        ?int $minutesLate = null,
        ?string $note = null,
    ): void {
        StudentAttendanceEntryRecord::updateOrCreate(
            [
                'session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'student_no' => $student->student_no,
                'student_name' => $student->full_name,
                'status' => $status,
                'minutes_late' => $status === 'late' ? $minutesLate : null,
                'note' => $note,
                'source_reference_type' => null,
                'source_reference_id' => null,
            ],
        );
    }

    private function upsertRevision(StudentAttendanceSessionRecord $session, string $reason, ?string $actorId): void
    {
        $revision = StudentAttendanceRevisionRecord::firstOrNew([
            'session_id' => $session->id,
            'reason' => $reason,
        ]);
        $revision->fill([
            'changed_by' => $actorId,
            'changed_at' => now()->subHour(),
            'summary' => [
                'status' => 'revised',
                'entry_count' => $session->entries()->count(),
            ],
        ]);
        $revision->save();
    }
}
