import type { Auth } from '@/types/auth';

export interface AccessControlRole {
    id: string;
    name: string;
    guard_name: string;
    permissions: string[];
    is_protected: boolean;
}

export interface AccessControlPermission {
    id: string;
    name: string;
    guard_name: string;
    label: string;
}

export interface AccessControlPermissionGroup {
    module: string;
    label: string;
    permissions: AccessControlPermission[];
}

export interface AccessControlPageProps {
    auth: Auth;
    roles: AccessControlRole[];
    permissionGroups: AccessControlPermissionGroup[];
    selectedRoleId: string | null;
    [key: string]: unknown;
}
