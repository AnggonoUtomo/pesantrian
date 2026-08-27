import type { OrganizationUnitStatus, OrganizationUnitType } from '../types';

export const statusLabels: Record<OrganizationUnitStatus, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
};

export const typeLabels: Record<OrganizationUnitType, string> = {
    foundation: 'Yayasan',
    pesantren: 'Pesantren',
    education_unit: 'Unit pendidikan',
    operational_unit: 'Unit operasional',
    dormitory: 'Asrama',
    other: 'Lainnya',
};

export const typeOptions = Object.entries(typeLabels) as [
    OrganizationUnitType,
    string,
][];
