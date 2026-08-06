import { Head, usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { ChangeUserStatusDialog } from '../components/ChangeUserStatusDialog';
import { DeleteUserDialog } from '../components/DeleteUserDialog';
import { ImpersonateUserDialog } from '../components/ImpersonateUserDialog';
import { RoleAssignmentDialog } from '../components/RoleAssignmentDialog';
import { UserFormDialog } from '../components/UserFormDialog';
import { UserShortcutBar } from '../components/UserShortcutBar';
import { UserSummaryCards } from '../components/UserSummaryCards';
import { UserTable } from '../components/UserTable';
import { UserViewDialog } from '../components/UserViewDialog';
import type {
    UserManagementDialogMode,
    UserManagementPageProps,
    UserManagementUser,
} from '../types';

export default function Index() {
    const { auth, users, roles, filters, errors } =
        usePage<UserManagementPageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [mode, setMode] = useState<UserManagementDialogMode | null>(null);
    const [selectedUser, setSelectedUser] = useState<UserManagementUser | null>(
        null,
    );
    const [impersonatingUser, setImpersonatingUser] =
        useState<UserManagementUser | null>(null);
    const [statusUser, setStatusUser] = useState<UserManagementUser | null>(
        null,
    );
    const [deletingUser, setDeletingUser] = useState<UserManagementUser | null>(
        null,
    );
    const [roleUser, setRoleUser] = useState<UserManagementUser | null>(null);
    const can = (permission: string) =>
        auth.superSystem === true || auth.permissions?.[permission] === true;
    const canView = can('user.view');
    const canCreate = can('user.create');
    const canEdit = can('user.update');
    const canImpersonate = can('user.impersonate');
    const canChangeStatus = can('user.status.manage');
    const canDelete = can('user.delete');
    const canAssignRole =
        can('user.update') && can('access_control.role.assign');
    const assignableRoles = auth.superSystem
        ? roles
        : roles.filter((role) => role.name !== 'SuperSystem');

    const closeModal = () => {
        setMode(null);
        setSelectedUser(null);
    };

    const openCreate = () => {
        setSelectedUser(null);
        setMode('create');
    };

    const openView = (user: UserManagementUser) => {
        setSelectedUser(user);
        setMode('view');
    };

    const openEdit = (user: UserManagementUser) => {
        setSelectedUser(user);
        setMode('edit');
    };

    const openImpersonate = (user: UserManagementUser) => {
        setImpersonatingUser(user);
    };

    useEffect(() => {
        const isTyping = (target: EventTarget | null) =>
            target instanceof HTMLElement &&
            (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
                target.isContentEditable);
        const handleShortcut = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                if (mode) {
                    closeModal();
                }

                if (impersonatingUser) {
                    setImpersonatingUser(null);
                }

                if (statusUser) {
                    setStatusUser(null);
                }

                if (deletingUser) {
                    setDeletingUser(null);
                }

                if (roleUser) {
                    setRoleUser(null);
                }

                return;
            }

            if (isTyping(event.target)) {
                return;
            }

            const key = event.key.toLowerCase();

            if (event.key === '/') {
                event.preventDefault();
                document.getElementById('user-search')?.focus();

                return;
            }

            if (event.shiftKey && key === 'a' && canCreate) {
                event.preventDefault();
                openCreate();
            }
        };
        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [
        canCreate,
        deletingUser,
        mode,
        impersonatingUser,
        roleUser,
        statusUser,
    ]);

    if (!canView) {
        return (
            <>
                <Head title="User Management" />
                <SystemDashboardLayout
                    title="User Management"
                    description="Kelola identity user pada area System."
                >
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-6 text-center">
                        <ShieldCheck className="mx-auto mb-3 size-10 text-rose-500" />
                        <h2 className="text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Anda tidak memiliki permission untuk melihat daftar
                            user.
                        </p>
                    </section>
                </SystemDashboardLayout>
            </>
        );
    }

    return (
        <>
            <Head title="User Management" />
            <SystemDashboardLayout
                title="User Management"
                description="Tinjau identity, status, dan akses user pada area System."
                actions={
                    canCreate ? (
                        <Button onClick={openCreate}>Tambah user</Button>
                    ) : null
                }
            >
                <div className="space-y-5">
                    <UserSummaryCards users={users} />
                    <UserShortcutBar />
                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Data user tidak dapat dimuat. Silakan coba lagi.
                        </p>
                    ) : null}
                    <UserTable
                        users={users}
                        search={search}
                        filters={filters}
                        roles={roles}
                        canCreate={canCreate}
                        canEdit={canEdit}
                        canImpersonate={canImpersonate}
                        canChangeStatus={canChangeStatus}
                        canDelete={canDelete}
                        canAssignRole={canAssignRole}
                        onSearchChange={setSearch}
                        onCreate={openCreate}
                        onView={openView}
                        onEdit={openEdit}
                        onImpersonate={openImpersonate}
                        onChangeStatus={setStatusUser}
                        onDelete={setDeletingUser}
                        onAssignRole={setRoleUser}
                    />
                </div>
            </SystemDashboardLayout>
            <UserViewDialog
                open={mode === 'view'}
                user={selectedUser}
                canEdit={canEdit}
                canImpersonate={
                    canImpersonate &&
                    selectedUser?.isProtected !== true &&
                    auth.impersonation == null
                }
                canAssignRole={
                    canAssignRole && selectedUser?.isProtected !== true
                }
                onOpenChange={(open) => !open && closeModal()}
                onEdit={() => selectedUser && openEdit(selectedUser)}
                onImpersonate={() => {
                    if (selectedUser) {
                        closeModal();
                        openImpersonate(selectedUser);
                    }
                }}
                onAssignRole={() => {
                    if (selectedUser) {
                        closeModal();
                        setRoleUser(selectedUser);
                    }
                }}
            />
            <UserFormDialog
                key={`${mode}-${selectedUser?.id ?? 'new'}`}
                open={mode === 'create' || mode === 'edit'}
                user={mode === 'edit' ? selectedUser : null}
                onOpenChange={(open) => !open && closeModal()}
            />
            <ImpersonateUserDialog
                open={impersonatingUser !== null}
                user={impersonatingUser}
                onOpenChange={(open) => !open && setImpersonatingUser(null)}
            />
            <ChangeUserStatusDialog
                key={`status-${statusUser?.id ?? 'new'}`}
                open={statusUser !== null}
                user={statusUser}
                onOpenChange={(open) => !open && setStatusUser(null)}
            />
            <DeleteUserDialog
                open={deletingUser !== null}
                user={deletingUser}
                onOpenChange={(open) => !open && setDeletingUser(null)}
            />
            <RoleAssignmentDialog
                key={`role-${roleUser?.id ?? 'new'}`}
                open={roleUser !== null}
                user={roleUser}
                roles={assignableRoles}
                onOpenChange={(open) => !open && setRoleUser(null)}
            />
        </>
    );
}
