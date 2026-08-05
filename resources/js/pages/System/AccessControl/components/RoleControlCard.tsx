import { Check, ChevronDown, Search, UsersRound } from 'lucide-react';
import {
    forwardRef,
    useEffect,
    useImperativeHandle,
    useMemo,
    useRef,
    useState,
} from 'react';
import type { ReactNode } from 'react';
import type { AccessControlRole } from '../types';

interface RoleControlCardProps {
    roles: AccessControlRole[];
    activeRole: AccessControlRole | null;
    onRoleChange: (roleId: string) => void;
    actions?: ReactNode;
}

export interface RoleControlCardHandle {
    openRoleSearch: () => void;
}

export const RoleControlCard = forwardRef<
    RoleControlCardHandle,
    RoleControlCardProps
>(function RoleControlCard({ roles, activeRole, onRoleChange, actions }, ref) {
    const [isRoleMenuOpen, setIsRoleMenuOpen] = useState(false);
    const [roleQuery, setRoleQuery] = useState('');
    const roleMenuRef = useRef<HTMLDivElement>(null);
    const roleSearchRef = useRef<HTMLInputElement>(null);
    const filteredRoles = useMemo(() => {
        const query = roleQuery.trim().toLowerCase();

        if (!query) {
            return roles;
        }

        return roles.filter((role) => role.name.toLowerCase().includes(query));
    }, [roleQuery, roles]);

    useImperativeHandle(
        ref,
        () => ({
            openRoleSearch: () => {
                setRoleQuery('');
                setIsRoleMenuOpen(true);
            },
        }),
        [],
    );

    useEffect(() => {
        if (!isRoleMenuOpen) {
            return;
        }

        roleSearchRef.current?.focus();
    }, [isRoleMenuOpen]);

    useEffect(() => {
        if (!isRoleMenuOpen) {
            return;
        }

        const handlePointerDown = (event: PointerEvent) => {
            if (!roleMenuRef.current?.contains(event.target as Node)) {
                setIsRoleMenuOpen(false);
            }
        };

        document.addEventListener('pointerdown', handlePointerDown);

        return () =>
            document.removeEventListener('pointerdown', handlePointerDown);
    }, [isRoleMenuOpen]);

    const chooseRole = (roleId: string) => {
        onRoleChange(roleId);
        setRoleQuery('');
        setIsRoleMenuOpen(false);
    };

    return (
        <section
            aria-labelledby="role-control-title"
            className="dashboard-card dashboard-module-card dashboard-card--blue rounded-2xl p-5"
        >
            <div className="flex items-center gap-3">
                <div className="dashboard-icon dashboard-accent--blue flex size-10 shrink-0 items-center justify-center rounded-lg">
                    <UsersRound aria-hidden="true" className="size-5" />
                </div>
                <div>
                    <h2
                        id="role-control-title"
                        className="text-sm font-semibold"
                    >
                        Role
                    </h2>
                    <p className="mt-1 text-xs text-foreground/70">
                        Pilih role untuk melihat permission yang dimiliki.
                    </p>
                </div>
            </div>
            <label
                className="mt-4 block text-sm font-medium"
                htmlFor="access-control-role"
            >
                Role aktif
            </label>
            <div ref={roleMenuRef} className="relative mt-2">
                <button
                    type="button"
                    id="access-control-role"
                    role="combobox"
                    aria-expanded={isRoleMenuOpen}
                    aria-controls="access-control-role-listbox"
                    aria-haspopup="listbox"
                    disabled={roles.length === 0}
                    onClick={() => setIsRoleMenuOpen((open) => !open)}
                    className="dashboard-control flex h-10 w-full items-center justify-between rounded-md px-3 text-left text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span className={activeRole ? '' : 'text-muted-foreground'}>
                        {activeRole?.name ?? 'Belum ada role'}
                    </span>
                    <ChevronDown
                        aria-hidden="true"
                        className={`size-4 transition-transform ${isRoleMenuOpen ? 'rotate-180' : ''}`}
                    />
                </button>
                {isRoleMenuOpen ? (
                    <div className="dashboard-role-combobox absolute z-20 mt-2 w-full rounded-lg border p-2 shadow-lg">
                        <div className="relative">
                            <Search
                                aria-hidden="true"
                                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <input
                                ref={roleSearchRef}
                                type="search"
                                value={roleQuery}
                                onChange={(event) =>
                                    setRoleQuery(event.target.value)
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Escape') {
                                        setIsRoleMenuOpen(false);
                                    }
                                }}
                                placeholder="Cari role..."
                                aria-label="Cari role"
                                className="dashboard-control h-9 w-full rounded-md pr-3 pl-9 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                        </div>
                        <div
                            id="access-control-role-listbox"
                            role="listbox"
                            aria-label="Daftar role"
                            className="mt-2 max-h-52 overflow-y-auto"
                        >
                            {filteredRoles.length === 0 ? (
                                <p className="px-3 py-2 text-sm text-muted-foreground">
                                    Role tidak ditemukan.
                                </p>
                            ) : (
                                filteredRoles.map((role) => (
                                    <button
                                        key={role.id}
                                        type="button"
                                        role="option"
                                        aria-selected={
                                            activeRole?.id === role.id
                                        }
                                        onClick={() => chooseRole(role.id)}
                                        className="dashboard-role-option flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm transition-colors outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    >
                                        <span>{role.name}</span>
                                        {activeRole?.id === role.id ? (
                                            <Check
                                                aria-hidden="true"
                                                className="size-4"
                                            />
                                        ) : null}
                                    </button>
                                ))
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
            {activeRole ? (
                <div className="dashboard-subcard dashboard-accent--blue mt-5 rounded-xl p-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <span className="font-medium">{activeRole.name}</span>
                        {activeRole.is_protected ? (
                            <span className="dashboard-badge dashboard-badge--amber rounded-full px-2 py-1 text-xs">
                                Protected
                            </span>
                        ) : null}
                    </div>
                    <p className="mt-2 text-xs text-foreground/70">
                        {activeRole.permissions.length} permission terpasang
                    </p>
                </div>
            ) : null}
            {actions ? (
                <div className="mt-5 flex flex-wrap gap-2 border-t border-border/70 pt-4">
                    {actions}
                </div>
            ) : null}
        </section>
    );
});
