import type { Auth } from '@/types/auth';

export type ReferenceOption = {
    id: string;
    code: string;
    name: string;
};

export type ClassGroupStatus = 'draft' | 'active' | 'closed' | 'archived';

export type ClassGroupArchiveFilter = 'active' | 'archived';

export type ClassGroupStudent = {
    id: string;
    student_id: string;
    student_no: string;
    student_name: string | null;
    joined_on: string;
    left_on: string | null;
    status: 'active' | 'transferred' | 'removed';
    reason: string | null;
};

export type ClassGroupHomeroom = {
    id: string;
    employee_id: string;
    employee_name: string;
    assigned_on: string;
    ended_on: string | null;
    status: 'active' | 'ended';
    reason: string | null;
};

export type ClassGroup = {
    id: string;
    academic_year: ReferenceOption;
    academic_term: ReferenceOption;
    unit: ReferenceOption;
    curriculum: ReferenceOption | null;
    class_level: ReferenceOption;
    code: string;
    name: string;
    capacity: number | null;
    status: ClassGroupStatus;
    archived_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    students?: ClassGroupStudent[];
    homerooms?: ClassGroupHomeroom[];
};

export type ClassGroupPage = {
    data: ClassGroup[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type ClassGroupFilters = {
    search?: string;
    filter?: {
        academic_year_id?: string;
        academic_term_id?: string;
        unit_id?: string;
        curriculum_id?: string;
        status?: ClassGroupStatus;
        archived?: ClassGroupArchiveFilter;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'code'
        | '-code'
        | 'name'
        | '-name'
        | 'capacity'
        | '-capacity'
        | 'status'
        | '-status';
};

export type ClassGroupIndexPageProps = {
    auth: Auth;
    classGroups: ClassGroupPage;
    filters: ClassGroupFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: {
        academicYears: ReferenceOption[];
        academicTerms: ReferenceOption[];
        units: ReferenceOption[];
        curricula: ReferenceOption[];
        classLevels: ReferenceOption[];
    };
    canManage: boolean;
    canPlacement: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type ClassGroupShowPageProps = {
    auth: Auth;
    classGroup: ClassGroup;
    options: {
        students: ReferenceOption[];
        employees: ReferenceOption[];
    };
    canManage: boolean;
    canPlacement: boolean;
    canArchive: boolean;
};
