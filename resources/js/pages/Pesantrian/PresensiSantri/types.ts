import type { Auth } from '@/types/auth';

export type ReferenceOption = {
    value: string;
    label: string;
};

export type StudentAttendanceContext =
    | 'class_group'
    | 'dormitory'
    | 'activity';
export type StudentAttendanceStatus =
    | 'draft'
    | 'submitted'
    | 'revised'
    | 'void';
export type StudentAttendanceEntryStatus =
    | 'present'
    | 'late'
    | 'excused'
    | 'sick'
    | 'absent';

export type StudentAttendanceSummary = {
    total: number;
    present: number;
    late: number;
    excused: number;
    sick: number;
    absent: number;
};

export type StudentAttendanceEntry = {
    id: string;
    student_id: string;
    student_no: string;
    student_name: string;
    status: StudentAttendanceEntryStatus;
    minutes_late: number | null;
    note: string | null;
    source_reference_type: string | null;
    source_reference_id: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type StudentAttendance = {
    id: string;
    attendance_date: string;
    context_type: StudentAttendanceContext;
    context_id: string | null;
    context_name: string;
    session_code: string;
    session_name: string;
    status: StudentAttendanceStatus;
    submitted_at: string | null;
    submitted_by: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
    created_by: string | null;
    created_at: string | null;
    updated_at: string | null;
    summary: StudentAttendanceSummary;
    entries?: StudentAttendanceEntry[];
};

export type StudentAttendancePage = {
    data: StudentAttendance[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type StudentAttendanceFilters = {
    search?: string;
    filter?: {
        date_from?: string;
        date_to?: string;
        context_type?: StudentAttendanceContext;
        status?: StudentAttendanceStatus;
    };
    page?: number | string;
    per_page?: number | string;
    sort?:
        | 'created_at'
        | '-created_at'
        | 'attendance_date'
        | '-attendance_date'
        | 'session_code'
        | '-session_code'
        | 'session_name'
        | '-session_name'
        | 'context_type'
        | '-context_type'
        | 'status'
        | '-status';
};

export type StudentAttendanceOptions = {
    contexts: ReferenceOption[];
    statuses: ReferenceOption[];
    students: {
        id: string;
        code: string;
        name: string;
    }[];
};

export type StudentAttendanceIndexPageProps = {
    auth: Auth;
    attendances: StudentAttendancePage;
    filters: StudentAttendanceFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    options: StudentAttendanceOptions;
    canManage: boolean;
    canSubmit: boolean;
    canRevise: boolean;
    canArchive: boolean;
    errors?: Record<string, string>;
};

export type StudentAttendanceShowPageProps = {
    auth: Auth;
    attendance: StudentAttendance;
    options: StudentAttendanceOptions;
    canManage: boolean;
    canSubmit: boolean;
    canRevise: boolean;
    canArchive: boolean;
};

export type StudentAttendanceEntryPayload = {
    student_id: string;
    status: StudentAttendanceEntryStatus;
    minutes_late: string | null;
    note: string | null;
    source_reference_type: string | null;
    source_reference_id: string | null;
};

export type StudentAttendanceSessionPayload = {
    attendance_date: string;
    context_type: StudentAttendanceContext;
    context_id: string | null;
    context_name: string;
    session_code: string;
    session_name: string;
    entries: StudentAttendanceEntryPayload[];
};

export type StudentAttendanceEntriesPayload = {
    entries: StudentAttendanceEntryPayload[];
};

export type StudentAttendanceReasonPayload = {
    reason: string;
};
