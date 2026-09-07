import type {
    StudentPermitStatus,
    StudentPermitSummary,
    StudentPermitType,
} from '../types';

export const permitTypeLabels: Record<StudentPermitType, string> = {
    leave: 'Keluar pesantren',
    home_visit: 'Pulang ke rumah',
    sick: 'Sakit / klinik',
    activity: 'Kegiatan khusus',
};

export const permitStatusLabels: Record<StudentPermitStatus, string> = {
    draft: 'Draft',
    submitted: 'Menunggu review',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    checked_out: 'Sedang izin',
    returned: 'Sudah kembali',
    void: 'Dibatalkan',
};

export function permitTypeLabel(type: StudentPermitType): string {
    return permitTypeLabels[type] ?? type;
}

export function permitStatusLabel(status: StudentPermitStatus): string {
    return permitStatusLabels[status] ?? status;
}

export function lifecycleSummary(summary: StudentPermitSummary): string {
    if (summary.is_late) {
        return 'Perlu perhatian: terlambat kembali';
    }

    if (summary.is_final) {
        return 'Siklus izin sudah selesai';
    }

    if (summary.is_active) {
        return 'Izin masih aktif/berjalan';
    }

    return 'Belum aktif';
}

export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return 'Belum diisi';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}
