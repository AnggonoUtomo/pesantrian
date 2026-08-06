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
import { useEffect, useRef, useState } from 'react';
import type { ComponentProps } from 'react';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import route from '@/lib/route';
import { cn } from '@/lib/utils';
import type {
    UserManagementFilters,
    UserManagementRole,
    UserManagementUser,
} from '../types';

type Props = {
    users: UserManagementUser[];
    search: string;
    filters: UserManagementFilters;
    roles: UserManagementRole[];
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

const statusAccentClasses: Record<UserManagementUser['status'], string> = {
    active: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    inactive:
        'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300',
    suspended:
        'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300',
};

type UserActionButtonProps = ComponentProps<typeof Button> & {
    tooltip: string;
};

function UserActionButton({
    children,
    className,
    tooltip,
    ...props
}: UserActionButtonProps) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button
                    size="icon"
                    variant="ghost"
                    className={cn('size-8', className)}
                    {...props}
                >
                    {children}
                </Button>
            </TooltipTrigger>
            <TooltipContent>{tooltip}</TooltipContent>
        </Tooltip>
    );
}

export function UserTable({
    users,
    search,
    filters,
    roles,
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
    const debounceTimer = useRef<number | null>(null);
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [role, setRole] = useState(filters.role ?? 'all');
    const [archive, setArchive] = useState(filters.archive);
    const hasActiveFilters =
        search.trim() !== '' ||
        status !== 'all' ||
        role !== 'all' ||
        archive !== 'all';
    const clearLiveSearchTimer = () => {
        if (debounceTimer.current !== null) {
            window.clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }
    };
    const requestFilters = (nextSearch = search) => {
        setIsSearching(true);
        router.get(
            route('system.users.index'),
            {
                search: nextSearch.trim() || undefined,
                status: status === 'all' ? undefined : status,
                role: role === 'all' ? undefined : role,
                archive: archive === 'all' ? undefined : archive,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setIsSearching(false),
            },
        );
    };
    const submitFilters = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        clearLiveSearchTimer();
        requestFilters();
    };
    const handleSearchChange = (value: string) => {
        onSearchChange(value);
        clearLiveSearchTimer();

        const query = value.trim();

        if (query.length > 0 && query.length < 3) {
            return;
        }

        debounceTimer.current = window.setTimeout(() => {
            requestFilters(value);
            debounceTimer.current = null;
        }, 400);
    };

    useEffect(() => {
        return () => {
            if (debounceTimer.current !== null) {
                window.clearTimeout(debounceTimer.current);
            }
        };
    }, []);

    const resetFilters = () => {
        clearLiveSearchTimer();
        onSearchChange('');
        setStatus('all');
        setRole('all');
        setArchive('all');
        setIsSearching(true);
        router.get(
            route('system.users.index'),
            {},
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
            <div className="flex flex-col gap-3 border-b p-4">
                <form
                    onSubmit={submitFilters}
                    className="flex min-w-0 flex-col gap-3"
                    role="search"
                >
                    <div className="flex min-w-0 flex-col gap-3 xl:flex-row">
                        <div className="relative min-w-0 flex-1">
                            <label htmlFor="user-search" className="sr-only">
                                Cari user
                            </label>
                            <Search
                                aria-hidden="true"
                                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="user-search"
                                value={search}
                                onChange={(event) =>
                                    handleSearchChange(event.target.value)
                                }
                                placeholder="Cari nama atau email..."
                                className="pl-9"
                            />
                            {search.trim().length > 0 &&
                            search.trim().length < 3 ? (
                                <p
                                    className="mt-1 text-xs text-muted-foreground"
                                    role="status"
                                >
                                    Live search aktif mulai 3 karakter.
                                </p>
                            ) : null}
                        </div>
                        <Select value={status} onValueChange={setStatus}>
                            <SelectTrigger
                                aria-label="Filter status user"
                                className="w-full xl:w-40"
                            >
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua status
                                </SelectItem>
                                <SelectItem value="active">Aktif</SelectItem>
                                <SelectItem value="inactive">
                                    Tidak aktif
                                </SelectItem>
                                <SelectItem value="suspended">
                                    Ditangguhkan
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={role} onValueChange={setRole}>
                            <SelectTrigger
                                aria-label="Filter role user"
                                className="w-full xl:w-44"
                            >
                                <SelectValue placeholder="Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua role</SelectItem>
                                {roles.map((roleOption) => (
                                    <SelectItem
                                        key={roleOption.id}
                                        value={roleOption.name}
                                    >
                                        {roleOption.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={archive}
                            onValueChange={(value) =>
                                setArchive(
                                    value as UserManagementFilters['archive'],
                                )
                            }
                        >
                            <SelectTrigger
                                aria-label="Filter arsip user"
                                className="w-full xl:w-40"
                            >
                                <SelectValue placeholder="Arsip" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua user</SelectItem>
                                <SelectItem value="active">
                                    User aktif
                                </SelectItem>
                                <SelectItem value="archived">
                                    Arsip saja
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-wrap gap-2">
                            <Button
                                type="submit"
                                variant="outline"
                                disabled={isSearching}
                            >
                                {isSearching ? 'Memuat...' : 'Terapkan filter'}
                            </Button>
                            {hasActiveFilters ? (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    disabled={isSearching}
                                    onClick={resetFilters}
                                >
                                    Reset filter
                                </Button>
                            ) : null}
                        </div>
                        {canCreate ? (
                            <Button
                                onClick={onCreate}
                                className="gap-2 sm:w-auto"
                            >
                                <Plus className="size-4" />
                                Tambah user
                            </Button>
                        ) : null}
                    </div>
                </form>
            </div>
            {users.length === 0 ? (
                <div role="status" className="p-10 text-center">
                    <UserRound className="mx-auto mb-3 size-9 text-muted-foreground" />
                    <h2 className="font-semibold">
                        {hasActiveFilters
                            ? 'Tidak ada user yang cocok'
                            : 'Belum ada user'}
                    </h2>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {hasActiveFilters
                            ? 'Ubah atau reset filter untuk melihat user lain.'
                            : 'Belum ada user yang dapat ditampilkan.'}
                    </p>
                    {hasActiveFilters ? (
                        <Button
                            type="button"
                            variant="outline"
                            className="mt-4"
                            onClick={resetFilters}
                        >
                            Reset filter
                        </Button>
                    ) : null}
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
                                        <Badge
                                            variant="outline"
                                            className={cn(
                                                'gap-1.5 font-medium',
                                                statusAccentClasses[
                                                    user.status
                                                ],
                                            )}
                                        >
                                            {statusLabels[user.status]}
                                        </Badge>
                                    </td>
                                    <td className="px-5 py-4">
                                        {user.isProtected ? (
                                            <Badge className="gap-1.5 border border-emerald-500/30 bg-emerald-500/10 text-emerald-700 shadow-none hover:bg-emerald-500/15 dark:text-emerald-300">
                                                <ShieldCheck className="size-3.5" />
                                                Terlindungi
                                            </Badge>
                                        ) : (
                                            <Badge
                                                variant="outline"
                                                className="border-sky-500/25 bg-sky-500/8 text-sky-700 dark:text-sky-300"
                                            >
                                                Standard
                                            </Badge>
                                        )}
                                    </td>
                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-1">
                                            <UserActionButton
                                                aria-label={`Lihat ${user.name}`}
                                                onClick={() => onView(user)}
                                                tooltip={`Lihat detail ${user.name}`}
                                                className="text-sky-600 hover:bg-sky-500/10 hover:text-sky-700 dark:text-sky-300 dark:hover:text-sky-200"
                                            >
                                                <Eye className="size-4" />
                                            </UserActionButton>
                                            {canEdit && !user.isProtected ? (
                                                <UserActionButton
                                                    aria-label={`Edit ${user.name}`}
                                                    onClick={() => onEdit(user)}
                                                    tooltip={`Edit ${user.name}`}
                                                    className="text-violet-600 hover:bg-violet-500/10 hover:text-violet-700 dark:text-violet-300 dark:hover:text-violet-200"
                                                >
                                                    <Edit3 className="size-4" />
                                                </UserActionButton>
                                            ) : null}
                                            {canImpersonate &&
                                            !user.isProtected ? (
                                                <UserActionButton
                                                    aria-label={`Impersonate ${user.name}`}
                                                    onClick={() =>
                                                        onImpersonate(user)
                                                    }
                                                    tooltip={`Masuk sebagai ${user.name}`}
                                                    className="text-amber-600 hover:bg-amber-500/10 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200"
                                                >
                                                    <LogIn className="size-4" />
                                                </UserActionButton>
                                            ) : null}
                                            {canChangeStatus &&
                                            !user.isProtected ? (
                                                <UserActionButton
                                                    aria-label={`Ubah status ${user.name}`}
                                                    onClick={() =>
                                                        onChangeStatus(user)
                                                    }
                                                    tooltip={`Ubah status ${user.name}`}
                                                    className="text-cyan-600 hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-cyan-300 dark:hover:text-cyan-200"
                                                >
                                                    <Activity className="size-4" />
                                                </UserActionButton>
                                            ) : null}
                                            {canDelete && !user.isProtected ? (
                                                <UserActionButton
                                                    aria-label={`Arsipkan ${user.name}`}
                                                    onClick={() =>
                                                        onDelete(user)
                                                    }
                                                    tooltip={`Arsipkan ${user.name}`}
                                                    className="text-rose-600 hover:bg-rose-500/10 hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200"
                                                >
                                                    <Trash2 className="size-4" />
                                                </UserActionButton>
                                            ) : null}
                                            {canAssignRole &&
                                            !user.isProtected ? (
                                                <UserActionButton
                                                    aria-label={`Atur role ${user.name}`}
                                                    onClick={() =>
                                                        onAssignRole(user)
                                                    }
                                                    tooltip={`Atur role ${user.name}`}
                                                    className="text-fuchsia-600 hover:bg-fuchsia-500/10 hover:text-fuchsia-700 dark:text-fuchsia-300 dark:hover:text-fuchsia-200"
                                                >
                                                    <ShieldPlus className="size-4" />
                                                </UserActionButton>
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
