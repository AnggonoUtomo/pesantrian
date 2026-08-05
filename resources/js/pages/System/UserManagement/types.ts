import type { Auth } from '@/types/auth';

export type UserManagementUser = {
    id: string;
    name: string;
    email: string;
    status: 'active' | 'inactive' | 'suspended';
    isProtected: boolean;
    deletedAt: string | null;
};

export type UserManagementPageProps = {
    auth: Auth;
    users: UserManagementUser[];
    filters: {
        search: string | null;
    };
    errors?: Record<string, string>;
};

export type UserManagementDialogMode = 'view' | 'create' | 'edit';
