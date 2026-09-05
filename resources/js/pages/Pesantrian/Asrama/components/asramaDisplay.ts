import type { DormitoryGenderPolicy, DormitoryStatus } from '../types';

export const dormitoryStatusOptions: [DormitoryStatus, string][] = [
    ['active', 'Aktif'],
    ['inactive', 'Nonaktif'],
    ['archived', 'Diarsipkan'],
];

export const genderPolicyOptions: [DormitoryGenderPolicy, string][] = [
    ['male', 'Putra'],
    ['female', 'Putri'],
    ['mixed', 'Campuran'],
    ['unspecified', 'Belum ditentukan'],
];

export function dormitoryStatusLabel(status: DormitoryStatus): string {
    return (
        dormitoryStatusOptions.find(([value]) => value === status)?.[1] ??
        status
    );
}

export function genderPolicyLabel(policy: DormitoryGenderPolicy): string {
    return (
        genderPolicyOptions.find(([value]) => value === policy)?.[1] ?? policy
    );
}

export function occupancyLabel(occupied: number, capacity: number): string {
    if (capacity <= 0) {
        return `${occupied}/0`;
    }

    return `${occupied}/${capacity}`;
}
