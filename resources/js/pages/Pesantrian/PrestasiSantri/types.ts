import type { Auth } from '@/types/auth';

export type AchievementStatus =
    | 'draft'
    | 'submitted'
    | 'needs_revision'
    | 'verified'
    | 'void';

export type AchievementLevel =
    | 'internal'
    | 'district'
    | 'city'
    | 'province'
    | 'national'
    | 'international';

export type AchievementType =
    | 'competition'
    | 'award'
    | 'publication'
    | 'delegation'
    | 'other';

export type SelectOption = {
    value: string;
    label: string;
    id?: string;
    code?: string;
    name?: string;
    description?: string | null;
    status?: 'active' | 'archived';
};

export type StudentAchievementCategory = {
    id: string;
    code: string;
    name: string;
    description: string | null;
    status: 'active' | 'archived';
    created_at: string | null;
    updated_at: string | null;
};

export type StudentAchievementSummary = {
    is_final: boolean;
    needs_action: boolean;
    revision_count: number;
};

export type StudentAchievementRevision = {
    id: string;
    achievement_id: string;
    from_status: AchievementStatus | null;
    to_status: AchievementStatus;
    reason: string;
    changed_by: string | null;
    changed_at: string;
    summary: Record<string, unknown> | null;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentAchievement = {
    id: string;
    achievement_no: string;
    category: StudentAchievementCategory;
    student_id: string;
    student_no: string;
    student_name: string;
    academic_period_id: string | null;
    academic_period_label: string | null;
    mentor_employee_id: string | null;
    mentor_name: string | null;
    title: string;
    achievement_type: AchievementType;
    level: AchievementLevel;
    result: string;
    organizer: string | null;
    event_name: string | null;
    event_location: string | null;
    achieved_on: string | null;
    period_started_on: string | null;
    period_ended_on: string | null;
    description: string | null;
    notes: string | null;
    status: AchievementStatus;
    submitted_at: string | null;
    submitted_by: string | null;
    verified_at: string | null;
    verified_by: string | null;
    verification_note: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
    created_by: string | null;
    created_at: string | null;
    updated_at: string | null;
    summary: StudentAchievementSummary;
    revisions?: StudentAchievementRevision[];
};

export type StudentAchievementFilterState = {
    date_from?: string;
    date_to?: string;
    status?: string;
    level?: string;
    category_id?: string;
    student_id?: string;
    mentor_employee_id?: string;
    academic_period_id?: string;
};

export type StudentAchievementIndexPageProps = {
    auth: Auth;
    achievements: {
        data: StudentAchievement[];
        meta: PaginationMeta;
    };
    filters: {
        search?: string;
        filter?: StudentAchievementFilterState;
        page?: number;
        per_page?: number;
        sort?: string;
    };
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: StudentAchievementOptions;
    errors?: Record<string, string>;
    canManage: boolean;
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
};

export type StudentAchievementMutationPayload = {
    student_id: string;
    category_id: string;
    academic_period_id: string | null;
    mentor_employee_id: string | null;
    title: string;
    achievement_type: AchievementType;
    level: AchievementLevel;
    result: string;
    organizer: string | null;
    event_name: string | null;
    event_location: string | null;
    achieved_on: string;
    period_started_on: string;
    period_ended_on: string;
    description: string | null;
    notes: string | null;
    revision_reason?: string;
};

export type StudentAchievementCategoryPayload = {
    code: string;
    name: string;
    description: string | null;
};

export type StudentAchievementVerifyPayload = {
    verification_note: string | null;
};

export type StudentAchievementRevisionPayload = {
    verification_note: string;
};

export type StudentAchievementVoidPayload = {
    void_reason: string;
};

export type StudentAchievementCategoryArchivePayload = {
    reason: string;
};

export type StudentAchievementShowPageProps = {
    auth: Auth;
    achievement: StudentAchievement;
    options: StudentAchievementOptions;
    canManage: boolean;
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
};

export type StudentAchievementOptions = {
    categories: SelectOption[];
    students: SelectOption[];
    officers: SelectOption[];
    academicPeriods: SelectOption[];
    levels: SelectOption[];
    statuses: SelectOption[];
    types: SelectOption[];
};

export type PaginationMeta = {
    currentPage: number;
    perPage: number;
    total: number;
    lastPage: number;
};
