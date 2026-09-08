import type {
    StudentDisciplineSeverity,
    StudentDisciplineStatus,
    StudentDisciplineSummary,
} from '../types';

export function disciplineStatusLabel(status: StudentDisciplineStatus): string {
    const labels: Record<StudentDisciplineStatus, string> = {
        draft: 'Draft',
        submitted: 'Menunggu review',
        in_review: 'Dalam review',
        action_assigned: 'Tindakan ditetapkan',
        resolved: 'Selesai',
        void: 'Dibatalkan',
    };

    return labels[status] ?? status;
}

export function disciplineSeverityLabel(
    severity: StudentDisciplineSeverity,
): string {
    const labels: Record<StudentDisciplineSeverity, string> = {
        minor: 'Ringan',
        moderate: 'Sedang',
        major: 'Berat',
    };

    return labels[severity] ?? severity;
}

export function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
    }).format(new Date(value));
}

export function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function pointsLabel(points: number | null): string {
    if (points === null) {
        return 'Tanpa poin';
    }

    return `${points} poin`;
}

export function lifecycleSummary(summary: StudentDisciplineSummary): string {
    if (summary.is_final) {
        return 'Kasus sudah final.';
    }

    if (summary.needs_action) {
        return 'Butuh tindak lanjut pembinaan.';
    }

    return 'Masih dalam proses administrasi.';
}
