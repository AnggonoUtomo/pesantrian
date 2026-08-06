import type { Auth } from '@/types/auth';

export type AuditLogRecord = {
    id: string;
    eventId: string;
    actorId: string | null;
    actorName: string | null;
    action: string;
    subjectType: string;
    subjectId: string | null;
    module: string;
    correlationId: string;
    reason: string | null;
    metadata: Record<string, unknown>;
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
};

export type AuditLogPageProps = {
    auth: Auth;
    auditLogs: AuditLogPage;
    filters: AuditLogFilters;
    errors?: Record<string, string>;
};
