import type { Auth } from '@/types/auth';

export type EmployeeStatus = 'active' | 'inactive';

export type EmployeeType =
    | 'teacher'
    | 'ustadz'
    | 'musyrif'
    | 'finance_staff'
    | 'administration_staff'
    | 'unit_head'
    | 'staff';

export type EmployeePrimaryUnit = {
    id: string;
    code: string;
    name: string;
    status: string;
};

export type Employee = {
    id: string;
    primary_unit_id: string | null;
    primary_unit: EmployeePrimaryUnit | null;
    employee_no: string;
    name: string;
    preferred_name: string | null;
    employment_type: EmployeeType;
    position: string | null;
    status: EmployeeStatus;
    joined_on: string | null;
    left_on: string | null;
    notes: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type EmployeePage = {
    data: Employee[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type EmployeeFilters = {
    search?: string;
    filter?: {
        status?: EmployeeStatus;
        employment_type?: EmployeeType;
        primary_unit_id?: string;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'employee_no'
        | '-employee_no'
        | 'name'
        | '-name'
        | 'joined_on'
        | '-joined_on';
};

export type HumanResourceEmployeePageProps = {
    auth: Auth;
    employees: EmployeePage;
    filters: EmployeeFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    errors?: Record<string, string>;
};
