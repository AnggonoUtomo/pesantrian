import type { PrimaryUnitOption, StudentStatus } from '../types';

export const studentStatusOptions: [StudentStatus, string][] = [
    ['active', 'Aktif'],
    ['inactive', 'Nonaktif'],
    ['transferred', 'Pindah'],
    ['graduated', 'Lulus'],
];

export const studentStatusLabels: Record<StudentStatus, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
    transferred: 'Pindah',
    graduated: 'Lulus',
};

export const genderLabels = {
    male: 'Laki-laki',
    female: 'Perempuan',
} as const;

export function primaryUnitNameMap(
    options: PrimaryUnitOption[],
): Map<string, string> {
    return new Map(
        options.map((unit) => [unit.id, `${unit.name} (${unit.code})`]),
    );
}

export function primaryUnitLabel(
    unitId: string | null,
    unitNameById: Map<string, string>,
): string {
    if (!unitId) {
        return 'Unit belum diisi';
    }

    return unitNameById.get(unitId) ?? 'Unit tidak ditemukan';
}
