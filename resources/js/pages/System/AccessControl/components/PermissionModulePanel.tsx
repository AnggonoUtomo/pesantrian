import { Button } from '@/components/ui/button';
import type { AccessControlPermissionGroup, AccessControlRole } from '../types';

interface PermissionModulePanelProps {
    activeRole: AccessControlRole | null;
    groups: AccessControlPermissionGroup[];
    canManage: boolean;
    selectedPermissions: string[];
    onPermissionChange: (permission: string, checked: boolean) => void;
    isDirty: boolean;
    isSaving: boolean;
    saveStatus: 'success' | 'error' | null;
    onSave: () => void;
}

export function PermissionModulePanel({
    activeRole,
    groups,
    canManage,
    selectedPermissions,
    onPermissionChange,
    isDirty,
    isSaving,
    saveStatus,
    onSave,
}: PermissionModulePanelProps) {
    if (!activeRole) {
        return (
            <section className="flex min-h-64 items-center justify-center rounded-xl border border-dashed bg-card p-6 text-center">
                <div>
                    <h2 className="font-semibold">Role belum tersedia</h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Permission akan tampil setelah role tersedia.
                    </p>
                </div>
            </section>
        );
    }

    return (
        <section
            aria-labelledby="permission-module-title"
            className="rounded-xl border bg-card p-5 shadow-xs"
        >
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2
                        id="permission-module-title"
                        className="text-sm font-semibold"
                    >
                        Permission Module
                    </h2>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Permission untuk role {activeRole.name}.
                    </p>
                </div>
                {!canManage || activeRole.is_protected ? (
                    <p className="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-800">
                        {activeRole.is_protected
                            ? 'Role SuperSystem dilindungi dan tidak dapat diubah.'
                            : 'Mode baca saja. Kamu tidak memiliki izin mengelola role.'}
                    </p>
                ) : null}
                {canManage && !activeRole.is_protected ? (
                    <Button
                        type="button"
                        size="sm"
                        disabled={!isDirty || isSaving}
                        onClick={onSave}
                    >
                        {isSaving ? 'Menyimpan...' : 'Simpan perubahan'}
                    </Button>
                ) : null}
            </div>
            {saveStatus === 'success' ? (
                <p className="mt-3 text-sm text-emerald-700">
                    Perubahan berhasil disimpan.
                </p>
            ) : null}
            {saveStatus === 'error' ? (
                <p className="mt-3 text-sm text-destructive">
                    Perubahan gagal disimpan.
                </p>
            ) : null}
            {groups.length === 0 ? (
                <div className="mt-5 rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                    Permission belum tersedia.
                </div>
            ) : (
                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                    {groups.map((group) => (
                        <div
                            key={group.module}
                            className="rounded-lg border p-4"
                        >
                            <h3 className="font-medium">{group.label}</h3>
                            <ul className="mt-3 space-y-2">
                                {group.permissions.map((permission) => (
                                    <li
                                        key={permission.id}
                                        className="flex items-start gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={selectedPermissions.includes(
                                                permission.name,
                                            )}
                                            disabled={
                                                activeRole.is_protected ||
                                                !canManage
                                            }
                                            onChange={(event) =>
                                                onPermissionChange(
                                                    permission.name,
                                                    event.target.checked,
                                                )
                                            }
                                            aria-label={permission.label}
                                            className="mt-0.5 size-4 rounded"
                                        />
                                        <span>
                                            <span className="block">
                                                {permission.label}
                                            </span>
                                            <span className="block text-xs break-all text-muted-foreground">
                                                {permission.name}
                                            </span>
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            )}
        </section>
    );
}
