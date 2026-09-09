<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseCandidateData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\LateStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitDisciplineSignalData;
use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceDisciplineSignalReader;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceDisciplineSignalData;

final readonly class ListStudentDisciplineCaseCandidates
{
    public function __construct(
        private StudentAttendanceDisciplineSignalReader $attendanceSignals,
        private LateStudentPermitReader $latePermits,
    ) {}

    /** @return list<StudentDisciplineCaseCandidateData> */
    public function execute(?string $dateFrom = null, ?string $dateTo = null, ?string $studentId = null, int $limitPerSource = 50): array
    {
        $candidates = [
            ...array_map(
                fn ($signal): StudentDisciplineCaseCandidateData => $this->fromAttendanceSignal($signal),
                $this->attendanceSignals->signals($dateFrom, $dateTo, $studentId, $limitPerSource),
            ),
            ...array_map(
                fn ($permit): StudentDisciplineCaseCandidateData => $this->fromPermitSignal($permit),
                $this->latePermits->lateReturns($dateFrom, $dateTo, $studentId, $limitPerSource),
            ),
        ];

        usort(
            $candidates,
            static fn (StudentDisciplineCaseCandidateData $left, StudentDisciplineCaseCandidateData $right): int => strcmp($right->occurredAt, $left->occurredAt),
        );

        return array_values($candidates);
    }

    private function fromAttendanceSignal(StudentAttendanceDisciplineSignalData $signal): StudentDisciplineCaseCandidateData
    {
        $isLate = $signal->status === 'late';
        $statusLabel = $isLate ? 'terlambat' : 'alfa';
        $minutes = $isLate && $signal->minutesLate !== null ? " selama {$signal->minutesLate} menit" : '';

        return new StudentDisciplineCaseCandidateData(
            sourceType: $isLate ? 'attendance_late' : 'attendance_absent',
            sourceId: $signal->entryId,
            studentId: $signal->studentId,
            studentNo: $signal->studentNo,
            studentName: $signal->studentName,
            occurredAt: $signal->attendanceDate.'T00:00:00.000000Z',
            title: "Presensi {$statusLabel}",
            description: "{$signal->studentName} tercatat {$statusLabel}{$minutes} pada {$signal->sessionName}.",
            suggestedSeverity: $isLate ? 'minor' : 'moderate',
            metadata: [
                'session_id' => $signal->sessionId,
                'session_code' => $signal->sessionCode,
                'session_name' => $signal->sessionName,
                'attendance_date' => $signal->attendanceDate,
                'context_type' => $signal->contextType,
                'context_name' => $signal->contextName,
                'attendance_status' => $signal->status,
                'minutes_late' => $signal->minutesLate,
                'note' => $signal->note,
            ],
        );
    }

    private function fromPermitSignal(StudentPermitDisciplineSignalData $permit): StudentDisciplineCaseCandidateData
    {
        return new StudentDisciplineCaseCandidateData(
            sourceType: 'permit_late_return',
            sourceId: $permit->permitId,
            studentId: $permit->studentId,
            studentNo: $permit->studentNo,
            studentName: $permit->studentName,
            occurredAt: $permit->occurredAt,
            title: 'Terlambat kembali dari izin',
            description: "{$permit->studentName} terlambat kembali dari izin {$permit->permitNo}.",
            suggestedSeverity: 'moderate',
            metadata: [
                'permit_id' => $permit->permitId,
                'permit_no' => $permit->permitNo,
                'permit_type' => $permit->permitType,
                'status' => $permit->status,
                'starts_at' => $permit->startsAt,
                'ends_at' => $permit->endsAt,
                'returned_at' => $permit->returnedAt,
                'destination' => $permit->destination,
                'return_note' => $permit->returnNote,
            ],
        );
    }
}
