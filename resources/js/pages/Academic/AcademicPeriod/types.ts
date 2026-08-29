import type { Auth } from '@/types/auth';

export type AcademicPeriodStatus = 'draft' | 'active' | 'closed';

export type AcademicYear = {
    id: string;
    code: string;
    name: string;
    starts_on: string;
    ends_on: string;
    status: AcademicPeriodStatus;
    created_at: string | null;
    updated_at: string | null;
};

export type AcademicTerm = {
    id: string;
    academic_year_id: string;
    code: string;
    name: string;
    sequence: number;
    starts_on: string;
    ends_on: string;
    status: AcademicPeriodStatus;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
};

export type AcademicPeriodPage<T> = {
    data: T[];
    meta: AcademicPeriodPaginationMeta;
};

export type AcademicPeriodPaginationMeta = {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
};

export type AcademicPeriodFilters = {
    year_search?: string | null;
    term_search?: string | null;
    year_status?: AcademicPeriodStatus | null;
    term_status?: AcademicPeriodStatus | null;
    per_page?: string | number | null;
};

export type AcademicPeriodPageProps = {
    auth: Auth;
    years: AcademicPeriodPage<AcademicYear>;
    terms: AcademicPeriodPage<AcademicTerm>;
    currentTerm: AcademicTerm | null;
    filters: AcademicPeriodFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    canManage: boolean;
};
