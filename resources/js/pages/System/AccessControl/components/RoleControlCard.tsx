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
            className="rounded-xl border bg-card p-5 shadow-xs"
        >
            <h2 id="role-control-title" className="text-sm font-semibold">
                Role
            </h2>
            <p className="mt-1 text-xs text-muted-foreground">
                Pilih role untuk melihat permission yang dimiliki.
            </p>
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
                className="mt-2 h-10 w-full rounded-md border bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
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
                <div className="mt-5 rounded-lg border bg-muted/30 p-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <span className="font-medium">{activeRole.name}</span>
                        {activeRole.is_protected ? (
                            <span className="rounded-full bg-amber-500/10 px-2 py-1 text-xs text-amber-700">
                                Protected
                            </span>
                        ) : null}
                    </div>
                    <p className="mt-2 text-xs text-muted-foreground">
                        {activeRole.permissions.length} permission terpasang
                    </p>
                </div>
            ) : null}
        </section>
    );
}
