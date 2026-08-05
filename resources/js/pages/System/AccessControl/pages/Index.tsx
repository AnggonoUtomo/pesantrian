import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import route from '@/lib/route';
import { AddRoleDialog } from '../components/AddRoleDialog';
import { DeleteRoleDialog } from '../components/DeleteRoleDialog';
import { PermissionModulePanel } from '../components/PermissionModulePanel';
import { RoleControlCard } from '../components/RoleControlCard';
import SystemDashboardLayout from '../layouts/system-dashboard-layout';
import type { AccessControlPageProps } from '../types';

export default function Index() {
    const { roles, permissionGroups, selectedRoleId, auth } =
        usePage<AccessControlPageProps>().props;
    const [activeRoleId, setActiveRoleId] = useState<string | null>(
        selectedRoleId,
    );
    const [rolePermissions, setRolePermissions] = useState<
        Record<string, string[]>
    >(() =>
        Object.fromEntries(roles.map((role) => [role.id, role.permissions])),
    );
    const [isSaving, setIsSaving] = useState(false);
    const [roleAction, setRoleAction] = useState<'create' | 'delete' | null>(
        null,
    );
    const [roleActionError, setRoleActionError] = useState<string | null>(null);
    const [saveStatus, setSaveStatus] = useState<'success' | 'error' | null>(
        null,
    );
    const activeRole = useMemo(
        () => roles.find((role) => role.id === activeRoleId) ?? null,
        [activeRoleId, roles],
    );
    const canManage = Boolean(
        auth.superSystem || auth.permissions?.['access_control.role.manage'],
    );
    const selectedPermissions = activeRole
        ? (rolePermissions[activeRole.id] ?? [])
        : [];

    const handlePermissionChange = (permission: string, checked: boolean) => {
        if (!activeRole || activeRole.is_protected || !canManage) {
            return;
        }

        setRolePermissions((current) => {
            const permissions = new Set(current[activeRole.id] ?? []);

            if (checked) {
                permissions.add(permission);
            } else {
                permissions.delete(permission);
            }

            return { ...current, [activeRole.id]: [...permissions] };
        });
        setSaveStatus(null);
    };

    const isDirty = activeRole
        ? [...selectedPermissions].sort().join('|') !==
          [...activeRole.permissions].sort().join('|')
        : false;

    const handleSave = () => {
        if (!activeRole || activeRole.is_protected || !canManage || !isDirty) {
            return;
        }

        setIsSaving(true);
        setSaveStatus(null);
        router.put(
            route('access-control.roles.permissions.update', activeRole.id),
            { permissions: selectedPermissions },
            {
                preserveScroll: true,
                onSuccess: () => setSaveStatus('success'),
                onError: () => setSaveStatus('error'),
                onFinish: () => setIsSaving(false),
            },
        );
    };

    const handleCreateRole = (name: string) => {
        setRoleAction('create');
        setRoleActionError(null);
        router.post(
            route('access-control.roles.store'),
            { name },
            {
                preserveScroll: true,
                preserveState: false,
                onError: (errors) =>
                    setRoleActionError(
                        Object.values(errors)[0] ??
                            'Role tidak dapat ditambahkan.',
                    ),
                onFinish: () => setRoleAction(null),
            },
        );
    };

    const handleDeleteRole = (role: (typeof roles)[number]) => {
        setRoleAction('delete');
        setRoleActionError(null);
        router.delete(route('access-control.roles.destroy', role.id), {
            preserveScroll: true,
            preserveState: false,
            onError: (errors) =>
                setRoleActionError(
                    Object.values(errors)[0] ?? 'Role tidak dapat dihapus.',
                ),
            onFinish: () => setRoleAction(null),
        });
    };

    return (
        <>
            <Head title="Access Control" />
            <SystemDashboardLayout
                title="Access Control"
                description="Pilih role, lalu tinjau dan kelola permission berdasarkan module."
            >
                <div className="dashboard-toolbar mb-4 flex flex-wrap justify-end gap-2">
                    {roleActionError ? (
                        <p
                            role="alert"
                            className="dashboard-message--error mr-auto self-center text-sm"
                        >
                            {roleActionError}
                        </p>
                    ) : null}
                    <AddRoleDialog
                        canManage={canManage}
                        isProcessing={roleAction === 'create'}
                        onSubmit={handleCreateRole}
                    />
                    <DeleteRoleDialog
                        role={activeRole}
                        canManage={canManage}
                        isProcessing={roleAction === 'delete'}
                        onSubmit={handleDeleteRole}
                    />
                </div>
                <div className="grid gap-4 xl:grid-cols-[280px_1fr]">
                    <RoleControlCard
                        roles={roles}
                        activeRole={activeRole}
                        onRoleChange={setActiveRoleId}
                    />
                    <PermissionModulePanel
                        activeRole={activeRole}
                        groups={permissionGroups}
                        canManage={canManage}
                        selectedPermissions={selectedPermissions}
                        onPermissionChange={handlePermissionChange}
                        isDirty={isDirty}
                        isSaving={isSaving}
                        saveStatus={saveStatus}
                        onSave={handleSave}
                    />
                </div>
            </SystemDashboardLayout>
        </>
    );
}
