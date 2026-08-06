import { ChevronDown, KeyRound, Search, ShieldCheck } from 'lucide-react';
import { useMemo, useState } from 'react';
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
    onSave,
}: PermissionModulePanelProps) {
    const [expandedGroups, setExpandedGroups] = useState<string[]>([]);
    const [permissionQuery, setPermissionQuery] = useState('');
    const filteredGroups = useMemo(() => {
        const query = permissionQuery.trim().toLowerCase();

        if (query === '') {
            return groups;
        }

        return groups
            .map((group) => ({
                ...group,
                permissions: group.permissions.filter((permission) =>
                    [permission.label, permission.name].some((value) =>
                        value.toLowerCase().includes(query),
                    ),
                ),
            }))
            .filter((group) => group.permissions.length > 0);
    }, [groups, permissionQuery]);

    const toggleGroup = (module: string): void => {
        setExpandedGroups((current) =>
            current.includes(module)
                ? current.filter((group) => group !== module)
                : [...current, module],
        );
    };

    const getGroupAccentClass = (module: string): string => {
        switch (module.toLowerCase().replace(/[\s_-]/g, '')) {
            case 'accesscontrol':
                return 'dashboard-accent--violet';
            case 'system':
                return 'dashboard-accent--emerald';
            default:
                return 'dashboard-accent--cyan';
        }
    };

    const isSecondaryColumnGroup = (module: string): boolean =>
        module.toLowerCase().replace(/[\s_-]/g, '') === 'system';

    if (!activeRole) {
        return (
            <section className="dashboard-card dashboard-module-card dashboard-card--violet flex min-h-64 items-center justify-center rounded-2xl border-dashed p-6 text-center">
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
            className="dashboard-card dashboard-module-card dashboard-card--violet rounded-2xl p-5"
        >
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="flex items-center gap-3">
                    <div className="dashboard-icon dashboard-accent--rose flex size-10 shrink-0 items-center justify-center rounded-lg">
                        <ShieldCheck aria-hidden="true" className="size-5" />
                    </div>
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
                </div>
                {!canManage || activeRole.is_protected ? (
                    <p className="dashboard-badge dashboard-badge--amber rounded-lg px-3 py-2 text-xs">
                        {activeRole.is_protected
                            ? 'Role SuperSystem dilindungi dan tidak dapat diubah.'
                            : 'Mode baca saja. Kamu tidak memiliki izin mengelola role.'}
                    </p>
                ) : null}
                {canManage && !activeRole.is_protected ? (
                    <Button
                        type="button"
                        size="sm"
                        className="dashboard-save-button"
                        disabled={!isDirty || isSaving}
                        onClick={onSave}
                    >
                        {isSaving ? 'Menyimpan...' : 'Simpan perubahan'}
                    </Button>
                ) : null}
            </div>
            {groups.length === 0 ? (
                <div className="dashboard-subcard mt-5 rounded-xl border-dashed p-6 text-center text-sm text-muted-foreground">
                    Permission belum tersedia.
                </div>
            ) : (
                <>
                    <div className="relative mt-5">
                        <Search
                            aria-hidden="true"
                            className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            id="access-control-permission-search"
                            name="permission-search"
                            type="search"
                            value={permissionQuery}
                            onChange={(event) =>
                                setPermissionQuery(event.target.value)
                            }
                            placeholder="Cari permission..."
                            aria-label="Cari permission"
                            className="dashboard-control h-10 w-full rounded-md pr-3 pl-9 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                    {filteredGroups.length === 0 ? (
                        <div className="dashboard-subcard mt-3 rounded-xl border-dashed p-6 text-center text-sm text-muted-foreground">
                            Permission tidak ditemukan.
                        </div>
                    ) : (
                        <div className="mt-3 grid items-start gap-3 sm:grid-cols-2">
                            {[false, true].map((isSecondaryColumn) => (
                                <div
                                    key={
                                        isSecondaryColumn
                                            ? 'secondary'
                                            : 'primary'
                                    }
                                    className="space-y-3"
                                >
                                    {filteredGroups
                                        .filter(
                                            (group) =>
                                                isSecondaryColumnGroup(
                                                    group.module,
                                                ) === isSecondaryColumn,
                                        )
                                        .map((group) => {
                                            const groupIndex =
                                                filteredGroups.indexOf(group);
                                            const isExpanded =
                                                expandedGroups.includes(
                                                    group.module,
                                                );
                                            const groupId = `permission-group-${groupIndex}`;
                                            const accentClass =
                                                getGroupAccentClass(
                                                    group.module,
                                                );

                                            return (
                                                <div
                                                    key={group.module}
                                                    className={`dashboard-subcard ${accentClass} rounded-xl p-4`}
                                                >
                                                    <button
                                                        type="button"
                                                        className="dashboard-permission-group-toggle flex w-full items-center justify-between gap-3 text-left font-medium"
                                                        aria-controls={groupId}
                                                        aria-expanded={
                                                            isExpanded
                                                        }
                                                        onClick={() =>
                                                            toggleGroup(
                                                                group.module,
                                                            )
                                                        }
                                                    >
                                                        <span className="flex items-center gap-2">
                                                            <span
                                                                className={`dashboard-icon ${accentClass} flex size-7 items-center justify-center rounded-md [&>svg]:size-4`}
                                                            >
                                                                <KeyRound aria-hidden="true" />
                                                            </span>
                                                            {group.label}
                                                        </span>
                                                        <ChevronDown
                                                            aria-hidden="true"
                                                            className={`size-4 shrink-0 transition-transform ${isExpanded ? 'rotate-180' : ''}`}
                                                        />
                                                    </button>
                                                    {isExpanded ? (
                                                        <ul
                                                            id={groupId}
                                                            className="mt-3 space-y-2"
                                                        >
                                                            {group.permissions.map(
                                                                (
                                                                    permission,
                                                                ) => (
                                                                    <li
                                                                        key={
                                                                            permission.id
                                                                        }
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
                                                                            onChange={(
                                                                                event,
                                                                            ) =>
                                                                                onPermissionChange(
                                                                                    permission.name,
                                                                                    event
                                                                                        .target
                                                                                        .checked,
                                                                                )
                                                                            }
                                                                            aria-label={
                                                                                permission.label
                                                                            }
                                                                            className="dashboard-permission-checkbox dashboard-accent--cyan mt-0.5 size-4 rounded"
                                                                        />
                                                                        <span>
                                                                            <span className="block">
                                                                                {
                                                                                    permission.label
                                                                                }
                                                                            </span>
                                                                            <span className="block text-xs break-all text-muted-foreground">
                                                                                {
                                                                                    permission.name
                                                                                }
                                                                            </span>
                                                                        </span>
                                                                    </li>
                                                                ),
                                                            )}
                                                        </ul>
                                                    ) : null}
                                                </div>
                                            );
                                        })}
                                </div>
                            ))}
                        </div>
                    )}
                </>
            )}
        </section>
    );
}
