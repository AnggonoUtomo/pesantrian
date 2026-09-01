import type { Auth } from '@/types/auth';

export type StudentStatus =
    | 'active'
    | 'inactive'
    | 'transferred'
    | 'graduated';

export type StudentGender = 'male' | 'female';

export type StudentArchiveFilter = 'active' | 'archived';

export type StudentGuardian = {
    id: string;
    student_id: string;
    guardian_name: string;
    guardian_phone: string | null;
    guardian_relation: string | null;
    is_primary: boolean;
    is_emergency_contact: boolean;
};

export type Student = {
    id: string;
    student_no: string;
    admission_id: string | null;
    registration_no: string | null;
    full_name: string;
    preferred_name: string | null;
    gender: StudentGender | null;
    birth_place: string | null;
    birth_date: string | null;
    previous_school: string | null;
    primary_unit_id: string | null;
    entry_date: string | null;
    status: StudentStatus;
    status_reason: string | null;
    status_changed_at: string | null;
    status_changed_by: string | null;
    archived_at: string | null;
    archived_by: string | null;
    primary_guardian: StudentGuardian | null;
    guardians?: StudentGuardian[];
    created_at: string | null;
    updated_at: string | null;
};

export type StudentPage = {
    data: Student[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type PrimaryUnitOption = {
    id: string;
    code: string;
    name: string;
};

export type StudentFilters = {
    search?: string;
    filter?: {
        status?: StudentStatus;
        primary_unit_id?: string;
        archived?: StudentArchiveFilter;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'student_no'
        | '-student_no'
        | 'full_name'
        | '-full_name'
        | 'entry_date'
        | '-entry_date';
};

export type StudentIndexPageProps = {
    auth: Auth;
    students: StudentPage;
    filters: StudentFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    primaryUnitOptions: PrimaryUnitOption[];
    errors?: Record<string, string>;
};

export type StudentShowPageProps = {
    auth: Auth;
    student: Student;
    primaryUnitOptions: PrimaryUnitOption[];
};

export type StudentMutationPayload = {
    full_name: string;
    preferred_name: string | null;
    gender: StudentGender | null;
    birth_place: string | null;
    birth_date: string | null;
    previous_school: string | null;
    primary_unit_id: string | null;
    entry_date: string | null;
    guardian_name: string;
    guardian_phone: string | null;
    guardian_relation: 'ayah' | 'ibu' | 'wali' | null;
    is_emergency_contact: boolean;
};

export type StudentLifecyclePayload = {
    status: StudentStatus;
    reason: string | null;
};

export type StudentArchivePayload = {
    reason: string | null;
};
