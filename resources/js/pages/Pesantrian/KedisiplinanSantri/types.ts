import type { Auth } from '@/types/auth';

export type StudentDisciplineSeverity = 'minor' | 'moderate' | 'major';

export type StudentDisciplineStatus =
    | 'draft'
    | 'submitted'
    | 'in_review'
    | 'action_assigned'
    | 'resolved'
    | 'void';

export type StudentDisciplineCategory = {
    id: string;
    code: string;
    name: string;
    description: string | null;
    default_severity: StudentDisciplineSeverity;
    default_points: number | null;
    status: string;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentDisciplineSummary = {
    is_final: boolean;
    needs_action: boolean;
    revision_count: number;
};

export type StudentDisciplineRevision = {
    id: string;
    case_id: string;
    reason: string;
    changed_by: string | null;
    changed_at: string;
    summary: Record<string, unknown> | null;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentDisciplineCase = {
    id: string;
    case_no: string;
    student_id: string;
    student_no: string;
    student_name: string;
    unit_id: string | null;
    unit_name: string | null;
    category: StudentDisciplineCategory;
    severity: StudentDisciplineSeverity;
    points: number | null;
    occurred_at: string;
    location: string | null;
    description: string;
    reported_by: string | null;
    assigned_employee_id: string | null;
    assigned_employee_name: string | null;
    status: StudentDisciplineStatus;
    submitted_at: string | null;
    reviewed_at: string | null;
    reviewed_by: string | null;
    review_note: string | null;
    action_plan: string | null;
    action_assigned_at: string | null;
    resolved_at: string | null;
    resolved_by: string | null;
    resolution_note: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
    created_by: string | null;
    created_at: string | null;
    updated_at: string | null;
    summary: StudentDisciplineSummary;
    revisions?: StudentDisciplineRevision[];
};

export type StudentDisciplinePage = {
    data: StudentDisciplineCase[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type StudentDisciplineOption = {
    value: string;
    label: string;
};

export type StudentDisciplineFilters = {
    search?: string;
    filter?: {
        date_from?: string;
        date_to?: string;
        status?: StudentDisciplineStatus;
        severity?: StudentDisciplineSeverity;
        category_id?: string;
        student_id?: string;
        assigned_employee_id?: string;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'case_no'
        | '-case_no'
        | 'student_name'
        | '-student_name'
        | 'status'
        | '-status'
        | 'severity'
        | '-severity'
        | 'occurred_at'
        | '-occurred_at';
};

export type StudentDisciplineIndexPageProps = {
    auth: Auth;
    cases: StudentDisciplinePage;
    filters: StudentDisciplineFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: {
        categories: StudentDisciplineOption[];
        students: StudentDisciplineOption[];
        officers: StudentDisciplineOption[];
        severities: StudentDisciplineOption[];
        statuses: StudentDisciplineOption[];
    };
    canManage: boolean;
    canReview: boolean;
    canResolve: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type StudentDisciplineShowPageProps = {
    auth: Auth;
    case: StudentDisciplineCase;
    options: StudentDisciplineIndexPageProps['options'];
    canManage: boolean;
    canReview: boolean;
    canResolve: boolean;
    canArchive: boolean;
};

export type StudentDisciplineMutationPayload = {
    student_id: string;
    category_id: string;
    severity: StudentDisciplineSeverity;
    points: string | number | null;
    occurred_at: string;
    location: string | null;
    description: string;
    assigned_employee_id: string | null;
    revision_reason?: string;
};

export type StudentDisciplineReviewPayload = {
    review_note: string;
};

export type StudentDisciplineActionPayload = {
    action_plan: string;
    assigned_employee_id: string | null;
};

export type StudentDisciplineResolvePayload = {
    resolution_note: string;
};

export type StudentDisciplineVoidPayload = {
    void_reason: string;
};
