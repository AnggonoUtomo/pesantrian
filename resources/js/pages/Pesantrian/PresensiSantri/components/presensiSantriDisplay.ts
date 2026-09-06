import type {
    StudentAttendanceContext,
    StudentAttendanceEntryStatus,
    StudentAttendanceStatus,
    StudentAttendanceSummary,
} from '../types';

export const contextLabels: Record<StudentAttendanceContext, string> = {
    class_group: 'Kelas / Rombel',
    dormitory: 'Asrama',
    activity: 'Kegiatan umum',
};

export const sessionStatusLabels: Record<StudentAttendanceStatus, string> = {
    draft: 'Draft',
    submitted: 'Submit',
    revised: 'Revisi',
    void: 'Dibatalkan',
};

export const entryStatusLabels: Record<StudentAttendanceEntryStatus, string> = {
    present: 'Hadir',
    late: 'Terlambat',
    excused: 'Izin',
    sick: 'Sakit',
    absent: 'Alfa',
};

export function contextLabel(context: StudentAttendanceContext): string {
    return contextLabels[context] ?? context;
}

export function sessionStatusLabel(status: StudentAttendanceStatus): string {
    return sessionStatusLabels[status] ?? status;
}

export function entryStatusLabel(status: StudentAttendanceEntryStatus): string {
    return entryStatusLabels[status] ?? status;
}

export function followUpCount(summary: StudentAttendanceSummary): number {
    return summary.late + summary.excused + summary.sick + summary.absent;
}

export function attendanceRatio(summary: StudentAttendanceSummary): string {
    if (summary.total === 0) {
        return '0%';
    }

    return `${Math.round((summary.present / summary.total) * 100)}%`;
}
