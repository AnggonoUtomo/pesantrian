import type {
    TahfidzSubmission,
    TahfidzSubmissionStatus,
    TahfidzSubmissionType,
} from '../types';

export const tahfidzTypeLabels: Record<TahfidzSubmissionType, string> = {
    new_memorization: 'Hafalan baru',
    murojaah: 'Murojaah',
};

export const tahfidzStatusLabels: Record<TahfidzSubmissionStatus, string> = {
    draft: 'Draft',
    submitted: 'Menunggu review',
    accepted: 'Diterima',
    needs_revision: 'Perlu koreksi',
    void: 'Dibatalkan',
};

export function tahfidzTypeLabel(type: TahfidzSubmissionType): string {
    return tahfidzTypeLabels[type] ?? type;
}

export function tahfidzStatusLabel(status: TahfidzSubmissionStatus): string {
    return tahfidzStatusLabels[status] ?? status;
}

export function memorizationRange(submission: TahfidzSubmission): string {
    const parts = [
        submission.juz ? `Juz ${submission.juz}` : null,
        submission.surah,
        submission.ayah_from && submission.ayah_to
            ? `ayat ${submission.ayah_from}-${submission.ayah_to}`
            : null,
    ].filter(Boolean);

    return parts.length > 0 ? parts.join(' · ') : 'Rentang belum diisi';
}

export function targetRange(submission: TahfidzSubmission): string {
    const target = submission.target;

    if (!target) {
        return 'Belum terhubung target';
    }

    const parts = [
        target.target_juz ? `Juz ${target.target_juz}` : null,
        target.target_surah,
        target.target_ayah_from && target.target_ayah_to
            ? `ayat ${target.target_ayah_from}-${target.target_ayah_to}`
            : null,
    ].filter(Boolean);

    return parts.length > 0 ? parts.join(' · ') : 'Target umum';
}
