import type { ClassGroupStatus, ReferenceOption } from '../types';

export const classGroupStatusLabels: Record<ClassGroupStatus, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Ditutup',
    archived: 'Diarsipkan',
};

export const classGroupStatusOptions: [ClassGroupStatus, string][] = [
    ['draft', 'Draft'],
    ['active', 'Aktif'],
    ['closed', 'Ditutup'],
    ['archived', 'Diarsipkan'],
];

export function referenceLabel(reference: ReferenceOption | null): string {
    if (!reference) {
        return 'Belum diisi';
    }

    return `${reference.name} (${reference.code})`;
}
