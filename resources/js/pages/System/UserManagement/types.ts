import type { Auth } from '@/types/auth';

export type UserManagementUser = {
    id: string;
    name: string;
    email: string;
    status: 'active' | 'inactive' | 'suspended';
    isProtected: boolean;
    deletedAt: string | null;
};

export type UserManagementRole = {
    id: string;
    name: string;
};

export type UserManagementFilters = {
    search: string | null;
    status: UserManagementUser['status'] | null;
    role: string | null;
    archive: 'all' | 'active' | 'archived';
    page: number;
    perPage: 5 | 10 | 25 | 50;
};

export type UserManagementPagination = {
    total: number;
    currentPage: number;
    lastPage: number;
    perPage: 5 | 10 | 25 | 50;
};

export type UserManagementPageProps = {
    auth: Auth;
    users: UserManagementUser[];
    roles: UserManagementRole[];
    filters: UserManagementFilters;
    pagination: UserManagementPagination;
    errors?: Record<string, string>;
};

export type UserManagementDialogMode = 'view' | 'create' | 'edit';
