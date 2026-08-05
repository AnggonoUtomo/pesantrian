import { Head, router, usePage } from '@inertiajs/react';
import { Keyboard } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import route from '@/lib/route';
import { AddRoleDialog } from '../components/AddRoleDialog';
import { DeleteRoleDialog } from '../components/DeleteRoleDialog';
import { PermissionModulePanel } from '../components/PermissionModulePanel';
import { RoleControlCard } from '../components/RoleControlCard';
import type { RoleControlCardHandle } from '../components/RoleControlCard';
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
    const roleControlRef = useRef<RoleControlCardHandle>(null);
    const activeRole = useMemo(
        () => roles.find((role) => role.id === activeRoleId) ?? null,
        [activeRoleId, roles],
    );
    const canManage = Boolean(
        auth.superSystem || auth.permissions?.['access_control.role.manage'],
    );
    const selectedPermissions = useMemo(
        () => (activeRole ? (rolePermissions[activeRole.id] ?? []) : []),
        [activeRole, rolePermissions],
    );

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
    };

    const isDirty = activeRole
        ? [...selectedPermissions].sort().join('|') !==
          [...activeRole.permissions].sort().join('|')
        : false;

    const handleSave = useCallback(() => {
        if (!activeRole || activeRole.is_protected || !canManage || !isDirty) {
            return;
        }

        setIsSaving(true);
        router.put(
            route('access-control.roles.permissions.update', activeRole.id),
            { permissions: selectedPermissions },
            {
                preserveScroll: true,
                onError: () => {
                    toast.error('Permission role tidak dapat diperbarui.');
                },
                onFinish: () => setIsSaving(false),
            },
        );
    }, [activeRole, canManage, isDirty, selectedPermissions]);

    useEffect(() => {
        const handleShortcut = (event: KeyboardEvent) => {
            const target = event.target;
            const isTyping =
                target instanceof HTMLElement &&
                (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
                    target.isContentEditable);

            if (isTyping) {
                return;
            }

            const key = event.key.toLowerCase();

            if (
                (key === 'r' || event.key === '/') &&
                !event.ctrlKey &&
                !event.metaKey &&
                !event.altKey
            ) {
                event.preventDefault();

                roleControlRef.current?.openRoleSearch();

                return;
            }

            if (
                key === 's' &&
                event.shiftKey &&
                !event.ctrlKey &&
                !event.metaKey &&
                !event.altKey
            ) {
                event.preventDefault();

                handleSave();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [handleSave]);

    const handleCreateRole = (name: string) => {
        setRoleAction('create');
        setRoleActionError(null);
        router.post(
            route('access-control.roles.store'),
            { name },
            {
                preserveScroll: true,
                preserveState: false,
                onError: (errors) => {
                    const message =
                        Object.values(errors)[0] ??
                        'Role tidak dapat ditambahkan.';
                    setRoleActionError(message);
                    toast.error(message);
                },
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
            onError: (errors) => {
                const message =
                    Object.values(errors)[0] ?? 'Role tidak dapat dihapus.';
                setRoleActionError(message);
                toast.error(message);
            },
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
                {roleActionError ? (
                    <p
                        role="alert"
                        className="dashboard-message--error mb-4 text-sm"
                    >
                        {roleActionError}
                    </p>
                ) : null}
                <div className="dashboard-shortcut-bar mb-4 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border px-3 py-2 text-xs">
                    <span className="flex items-center gap-2 font-medium">
                        <Keyboard aria-hidden="true" className="size-4" />
                        Shortcut
                    </span>
                    <span className="flex items-center gap-1.5">
                        <kbd>R</kbd> atau <kbd>/</kbd> cari role
                    </span>
                    <span className="flex items-center gap-1.5">
                        <kbd>Shift</kbd> + <kbd>S</kbd> simpan permission
                    </span>
                    <span className="flex items-center gap-1.5">
                        <kbd>Esc</kbd> tutup pencarian
                    </span>
                </div>
                <div className="grid items-start gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
                    <RoleControlCard
                        ref={roleControlRef}
                        roles={roles}
                        activeRole={activeRole}
                        onRoleChange={setActiveRoleId}
                        actions={
                            <>
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
                            </>
                        }
                    />
                    <PermissionModulePanel
                        activeRole={activeRole}
                        groups={permissionGroups}
                        canManage={canManage}
                        selectedPermissions={selectedPermissions}
                        onPermissionChange={handlePermissionChange}
                        isDirty={isDirty}
                        isSaving={isSaving}
                        onSave={handleSave}
                    />
                </div>
            </SystemDashboardLayout>
        </>
    );
}
