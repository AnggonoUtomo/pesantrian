import type { Auth } from '@/types/auth';

export type AuditLogRecord = {
    actorName: string | null;
    actionLabel: string;
    subjectLabel: string;
    moduleLabel: string;
    reason: string | null;
    securityContext: {
        browser: string | null;
        ipAddress: string | null;
    } | null;
    settingChange: {
        category: string;
        setting: string;
        beforeValue: string;
        afterValue: string;
    } | null;
    createdAt: string;
};

export type AuditLogPage = {
    data: AuditLogRecord[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};

export type AuditLogFilters = {
    search?: string;
    module?: string;
    action?: string;
    date_from?: string;
    date_to?: string;
    per_page?: number;
    sort_direction?: 'asc' | 'desc';
};

export type AuditLogPageProps = {
    auth: Auth;
    auditLogs: AuditLogPage;
    filters: AuditLogFilters;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    errors?: Record<string, string>;
};
