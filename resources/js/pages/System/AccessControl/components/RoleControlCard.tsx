import { UsersRound } from 'lucide-react';
import type { AccessControlRole } from '../types';

interface RoleControlCardProps {
    roles: AccessControlRole[];
    activeRole: AccessControlRole | null;
    onRoleChange: (roleId: string) => void;
}

export function RoleControlCard({
    roles,
    activeRole,
    onRoleChange,
}: RoleControlCardProps) {
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
            <select
                id="access-control-role"
                value={activeRole?.id ?? ''}
                onChange={(event) => onRoleChange(event.target.value)}
                className="dashboard-control mt-2 h-10 w-full rounded-md px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                disabled={roles.length === 0}
            >
                {roles.length === 0 ? (
                    <option value="">Belum ada role</option>
                ) : null}
                {roles.map((role) => (
                    <option key={role.id} value={role.id}>
                        {role.name}
                    </option>
                ))}
            </select>
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
        </section>
    );
}
