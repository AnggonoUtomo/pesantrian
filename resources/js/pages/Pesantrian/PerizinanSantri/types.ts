import type { Auth } from '@/types/auth';

export type StudentPermitType = 'leave' | 'home_visit' | 'sick' | 'activity';

export type StudentPermitStatus =
    | 'draft'
    | 'submitted'
    | 'approved'
    | 'rejected'
    | 'checked_out'
    | 'returned'
    | 'void';

export type StudentPermitSummary = {
    is_late: boolean;
    is_final: boolean;
    is_active: boolean;
    revision_count: number;
};

export type StudentPermitRevision = {
    id: string;
    permit_id: string;
    reason: string;
    changed_by: string | null;
    changed_at: string;
    summary: Record<string, unknown> | null;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentPermit = {
    id: string;
    permit_no: string;
    student_id: string;
    student_no: string;
    student_name: string;
    permit_type: StudentPermitType;
    starts_at: string;
    ends_at: string;
    destination: string | null;
    reason: string;
    guardian_name: string | null;
    guardian_phone: string | null;
    guardian_relation: string | null;
    status: StudentPermitStatus;
    submitted_at: string | null;
    submitted_by: string | null;
    reviewed_at: string | null;
    reviewed_by: string | null;
    review_note: string | null;
    checked_out_at: string | null;
    checked_out_by: string | null;
    returned_at: string | null;
    returned_by: string | null;
    return_note: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
    created_by: string | null;
    created_at: string | null;
    updated_at: string | null;
    summary: StudentPermitSummary;
    revisions?: StudentPermitRevision[];
};

export type StudentPermitPage = {
    data: StudentPermit[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type StudentPermitOption = {
    value: string;
    label: string;
};

export type StudentPermitFilters = {
    search?: string;
    filter?: {
        date_from?: string;
        date_to?: string;
        permit_type?: StudentPermitType;
        status?: StudentPermitStatus;
        student_id?: string;
        is_late?: boolean | string;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'permit_no'
        | '-permit_no'
        | 'student_name'
        | '-student_name'
        | 'permit_type'
        | '-permit_type'
        | 'status'
        | '-status'
        | 'starts_at'
        | '-starts_at'
        | 'ends_at'
        | '-ends_at';
};

export type StudentPermitIndexPageProps = {
    auth: Auth;
    permits: StudentPermitPage;
    filters: StudentPermitFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: {
        students: StudentPermitOption[];
        permitTypes: StudentPermitOption[];
        statuses: StudentPermitOption[];
    };
    canManage: boolean;
    canApprove: boolean;
    canCheckout: boolean;
    canReturn: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type StudentPermitShowPageProps = {
    auth: Auth;
    permit: StudentPermit;
    options: StudentPermitIndexPageProps['options'];
    canManage: boolean;
    canApprove: boolean;
    canCheckout: boolean;
    canReturn: boolean;
    canArchive: boolean;
};

export type StudentPermitMutationPayload = {
    student_id: string;
    permit_type: StudentPermitType;
    starts_at: string;
    ends_at: string;
    destination: string | null;
    reason: string;
    revision_reason?: string;
};

export type StudentPermitReasonPayload = {
    reason: string;
};

export type StudentPermitReviewPayload = {
    review_note: string | null;
};

export type StudentPermitReturnPayload = {
    returned_at: string;
    return_note: string | null;
};
