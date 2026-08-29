import type { EmployeeStatus, EmployeeType } from '../types';

export const employeeStatusLabels: Record<EmployeeStatus, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
};

export const employeeTypeLabels: Record<EmployeeType, string> = {
    teacher: 'Guru',
    ustadz: 'Ustadz',
    musyrif: 'Musyrif',
    finance_staff: 'Staff Finance',
    administration_staff: 'Staff Administrasi',
    unit_head: 'Kepala Unit',
    staff: 'Staff',
};

export const employeeTypeOptions = Object.entries(employeeTypeLabels) as [
    EmployeeType,
    string,
][];
