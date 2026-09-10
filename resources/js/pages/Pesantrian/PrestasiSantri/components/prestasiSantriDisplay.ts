import type {
    AchievementLevel,
    AchievementStatus,
    AchievementType,
    StudentAchievementSummary,
} from '../types';

export function achievementStatusLabel(status: AchievementStatus): string {
    const labels: Record<AchievementStatus, string> = {
        draft: 'Draft',
        submitted: 'Menunggu verifikasi',
        needs_revision: 'Perlu revisi',
        verified: 'Terverifikasi',
        void: 'Dibatalkan',
    };

    return labels[status] ?? status;
}

export function achievementLevelLabel(level: AchievementLevel): string {
    const labels: Record<AchievementLevel, string> = {
        internal: 'Internal',
        district: 'Kecamatan',
        city: 'Kabupaten/Kota',
        province: 'Provinsi',
        national: 'Nasional',
        international: 'Internasional',
    };

    return labels[level] ?? level;
}

export function achievementTypeLabel(type: AchievementType): string {
    const labels: Record<AchievementType, string> = {
        competition: 'Lomba/Kompetisi',
        award: 'Penghargaan',
        publication: 'Publikasi',
        delegation: 'Delegasi',
        other: 'Lainnya',
    };

    return labels[type] ?? type;
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

export function lifecycleSummary(summary: StudentAchievementSummary): string {
    if (summary.is_final) {
        return 'Riwayat prestasi sudah final.';
    }

    if (summary.needs_action) {
        return 'Butuh tindak lanjut verifikasi atau revisi.';
    }

    return 'Masih dalam proses pencatatan.';
}
