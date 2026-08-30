import type {
    AdmissionStatus,
    CandidateGender,
    RegistrationFeeStatus,
} from '../types';

export const admissionStatusLabels: Record<AdmissionStatus, string> = {
    draft: 'Draft',
    submitted: 'Diajukan',
    verified: 'Terverifikasi',
    accepted: 'Diterima',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
};

export const admissionStatusOptions = Object.entries(admissionStatusLabels);

export const registrationFeeStatusLabels: Record<
    RegistrationFeeStatus,
    string
> = {
    not_required: 'Tidak wajib',
    pending: 'Menunggu',
    verified: 'Terverifikasi',
    rejected: 'Ditolak',
};

export const registrationFeeStatusOptions = Object.entries(
    registrationFeeStatusLabels,
);

export const genderLabels: Record<CandidateGender, string> = {
    male: 'Laki-laki',
    female: 'Perempuan',
};

export function targetUnitLabel(
    targetUnitId: string | null,
    targetUnitNameById: Map<string, string>,
): string {
    if (!targetUnitId) {
        return 'Belum dipilih';
    }

    return targetUnitNameById.get(targetUnitId) ?? 'Unit tidak ditemukan';
}
