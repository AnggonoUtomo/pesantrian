import type { Auth } from '@/types/auth';

export type ReferenceOption = {
    value: string;
    label: string;
};

export type TahfidzSubmissionType = 'new_memorization' | 'murojaah';
export type TahfidzSubmissionStatus =
    | 'draft'
    | 'submitted'
    | 'accepted'
    | 'needs_revision'
    | 'void';

export type TahfidzProgram = {
    id: string;
    code: string;
    name: string;
    description: string | null;
    status: string;
};

export type TahfidzTarget = {
    id: string;
    student_id: string;
    student_no: string;
    student_name: string;
    academic_period_id: string | null;
    period_label: string | null;
    target_juz: number | null;
    target_surah: string | null;
    target_ayah_from: number | null;
    target_ayah_to: number | null;
    target_note: string | null;
    status: string;
};

export type TahfidzSubmissionSummary = {
    has_target: boolean;
    has_supervisor: boolean;
    has_revision: boolean;
    revision_count: number;
};

export type TahfidzSubmissionRevision = {
    id: string;
    reason: string;
    changed_by: string | null;
    changed_at: string;
    summary: Record<string, unknown>;
    created_at: string | null;
    updated_at: string | null;
};

export type TahfidzSubmission = {
    id: string;
    program: TahfidzProgram;
    target: TahfidzTarget | null;
    student_id: string;
    student_no: string;
    student_name: string;
    supervisor_id: string | null;
    supervisor_name: string | null;
    submission_date: string;
    type: TahfidzSubmissionType;
    juz: number | null;
    surah: string | null;
    ayah_from: number | null;
    ayah_to: number | null;
    status: TahfidzSubmissionStatus;
    quality_note: string | null;
    created_by: string | null;
    reviewed_at: string | null;
    reviewed_by: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
    created_at: string | null;
    updated_at: string | null;
    summary: TahfidzSubmissionSummary;
    revisions?: TahfidzSubmissionRevision[];
};

export type TahfidzSubmissionPage = {
    data: TahfidzSubmission[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type TahfidzFilters = {
    search?: string;
    filter?: {
        type?: TahfidzSubmissionType;
        status?: TahfidzSubmissionStatus;
        date_from?: string;
        date_to?: string;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'submission_date'
        | '-submission_date'
        | 'student_name'
        | '-student_name'
        | 'supervisor_name'
        | '-supervisor_name'
        | 'type'
        | '-type'
        | 'status'
        | '-status';
};

export type TahfidzOptions = {
    types: ReferenceOption[];
    statuses: ReferenceOption[];
};

export type TahfidzIndexPageProps = {
    auth: Auth;
    submissions: TahfidzSubmissionPage;
    filters: TahfidzFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: TahfidzOptions;
    canManage: boolean;
    canRecord: boolean;
    canReview: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type TahfidzShowPageProps = {
    auth: Auth;
    submission: TahfidzSubmission;
    options: TahfidzOptions;
    canManage: boolean;
    canRecord: boolean;
    canReview: boolean;
    canArchive: boolean;
};
