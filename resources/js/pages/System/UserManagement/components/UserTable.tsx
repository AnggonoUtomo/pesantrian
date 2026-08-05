import { router } from '@inertiajs/react';
import {
    Activity,
    Edit3,
    Eye,
    LogIn,
    Plus,
    Search,
    ShieldCheck,
    ShieldPlus,
    Trash2,
    UserRound,
} from 'lucide-react';
import { useState } from 'react';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import route from '@/lib/route';
import type { UserManagementUser } from '../types';

type Props = {
    users: UserManagementUser[];
    search: string;
    canCreate: boolean;
    canEdit: boolean;
    canImpersonate: boolean;
    canChangeStatus: boolean;
    canDelete: boolean;
    canAssignRole: boolean;
    onSearchChange: (value: string) => void;
    onCreate: () => void;
    onView: (user: UserManagementUser) => void;
    onEdit: (user: UserManagementUser) => void;
    onImpersonate: (user: UserManagementUser) => void;
    onChangeStatus: (user: UserManagementUser) => void;
    onDelete: (user: UserManagementUser) => void;
    onAssignRole: (user: UserManagementUser) => void;
};

const statusLabels: Record<UserManagementUser['status'], string> = {
    active: 'Aktif',
    inactive: 'Tidak aktif',
    suspended: 'Ditangguhkan',
};

export function UserTable({
    users,
    search,
    canCreate,
    canEdit,
    canImpersonate,
    canChangeStatus,
    canDelete,
    canAssignRole,
    onSearchChange,
    onCreate,
    onView,
    onEdit,
    onImpersonate,
    onChangeStatus,
    onDelete,
    onAssignRole,
}: Props) {
    const [isSearching, setIsSearching] = useState(false);
    const submitSearch = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsSearching(true);
        router.get(
            route('system.users.index'),
            { search: search || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setIsSearching(false),
            },
        );
    };

    return (
        <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
            <div className="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                <form
                    onSubmit={submitSearch}
                    className="flex min-w-0 flex-1 gap-2"
                    role="search"
                >
                    <label htmlFor="user-search" className="sr-only">
                        Cari user
                    </label>
                    <div className="relative min-w-0 flex-1">
                        <Search
                            aria-hidden="true"
                            className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="user-search"
                            value={search}
                            onChange={(event) =>
                                onSearchChange(event.target.value)
                            }
                            placeholder="Cari nama atau email..."
                            className="pl-9"
                        />
                    </div>
                    <Button
                        type="submit"
                        variant="outline"
                        disabled={isSearching}
                    >
                        {isSearching ? 'Memuat...' : 'Cari'}
                    </Button>
                </form>
                {canCreate ? (
                    <Button onClick={onCreate} className="gap-2 sm:w-auto">
                        <Plus className="size-4" />
                        Tambah user
                    </Button>
                ) : null}
            </div>
            {users.length === 0 ? (
                <div role="status" className="p-10 text-center">
                    <UserRound className="mx-auto mb-3 size-9 text-muted-foreground" />
                    <h2 className="font-semibold">Belum ada user</h2>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Tidak ada user yang cocok dengan pencarian.
                    </p>
                </div>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[680px] text-left text-sm">
                        <thead className="dashboard-table-header border-b text-xs tracking-wide text-foreground/80 uppercase">
                            <tr>
                                <th className="px-5 py-3 font-medium">User</th>
                                <th className="px-5 py-3 font-medium">
                                    Status
                                </th>
                                <th className="px-5 py-3 font-medium">
                                    Perlindungan
                                </th>
                                <th className="px-5 py-3 text-right font-medium">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border/70">
                            {users.map((user, index) => (
                                <tr
                                    key={user.id}
                                    className="dashboard-table-row transition-colors"
                                >
                                    <td className="px-5 py-4">
                                        <button
                                            id={`user-table-row-${index}`}
                                            type="button"
                                            onClick={() => onView(user)}
                                            className="flex items-center gap-3 rounded-md text-left outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >
                                            <Avatar className="size-9 rounded-lg">
                                                <AvatarFallback className="rounded-lg bg-primary/10 text-primary">
                                                    {user.name
                                                        .split(' ')
                                                        .map((part) => part[0])
                                                        .slice(0, 2)
                                                        .join('')
                                                        .toUpperCase()}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">
                                                    {user.name}
                                                </span>
                                                <span className="block truncate text-xs text-muted-foreground">
                                                    {user.email}
                                                </span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-5 py-4">
                                        <Badge variant="outline">
                                            {statusLabels[user.status]}
                                        </Badge>
                                    </td>
                                    <td className="px-5 py-4">
                                        {user.isProtected ? (
                                            <span className="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                                <ShieldCheck className="size-4" />{' '}
                                                Protected
                                            </span>
                                        ) : (
                                            <span className="text-xs text-muted-foreground">
                                                Standard
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                aria-label={`Lihat ${user.name}`}
                                                onClick={() => onView(user)}
                                            >
                                                <Eye className="size-4" />
                                            </Button>
                                            {canEdit && !user.isProtected ? (
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    aria-label={`Edit ${user.name}`}
                                                    onClick={() => onEdit(user)}
                                                >
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            ) : null}
                                            {canImpersonate &&
                                            !user.isProtected ? (
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    aria-label={`Impersonate ${user.name}`}
                                                    onClick={() =>
                                                        onImpersonate(user)
                                                    }
                                                >
                                                    <LogIn className="size-4" />
                                                </Button>
                                            ) : null}
                                            {canChangeStatus &&
                                            !user.isProtected ? (
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    aria-label={`Ubah status ${user.name}`}
                                                    onClick={() =>
                                                        onChangeStatus(user)
                                                    }
                                                >
                                                    <Activity className="size-4" />
                                                </Button>
                                            ) : null}
                                            {canDelete && !user.isProtected ? (
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    aria-label={`Arsipkan ${user.name}`}
                                                    onClick={() =>
                                                        onDelete(user)
                                                    }
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            ) : null}
                                            {canAssignRole &&
                                            !user.isProtected ? (
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    aria-label={`Atur role ${user.name}`}
                                                    onClick={() =>
                                                        onAssignRole(user)
                                                    }
                                                >
                                                    <ShieldPlus className="size-4" />
                                                </Button>
                                            ) : null}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
}
