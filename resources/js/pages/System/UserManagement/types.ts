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
};

export type UserManagementPageProps = {
    auth: Auth;
    users: UserManagementUser[];
    roles: UserManagementRole[];
    filters: UserManagementFilters;
    errors?: Record<string, string>;
};

export type UserManagementDialogMode = 'view' | 'create' | 'edit';
