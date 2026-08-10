import { router } from '@inertiajs/react';
import {
    Activity,
    Edit3,
    Eye,
    LogIn,
    Plus,
    RotateCcw,
    Search,
    ShieldPlus,
    Trash2,
    UserRound,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ComponentProps } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
    UserManagementPagination,
    UserManagementRole,
    UserManagementUser,
} from '../types';
import { BulkUserLifecycleDialog } from './BulkUserLifecycleDialog';

type Props = {
    users: UserManagementUser[];
    search: string;
    filters: UserManagementFilters;
    pagination: UserManagementPagination;
    roles: UserManagementRole[];
    canCreate: boolean;
    canEdit: boolean;
    canImpersonate: boolean;
    canChangeStatus: boolean;
    canDelete: boolean;
    canRestore: boolean;
    canForceDelete: boolean;
    canAssignRole: boolean;
    onSearchChange: (value: string) => void;
    onCreate: () => void;
    onView: (user: UserManagementUser) => void;
    onEdit: (user: UserManagementUser) => void;
    onImpersonate: (user: UserManagementUser) => void;
    onChangeStatus: (user: UserManagementUser) => void;
    onDelete: (user: UserManagementUser) => void;
    onRestore: (user: UserManagementUser) => void;
    onForceDelete: (user: UserManagementUser) => void;
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
    pagination,
    roles,
    canCreate,
    canEdit,
    canImpersonate,
    canChangeStatus,
    canDelete,
    canRestore,
    canForceDelete,
    canAssignRole,
    onSearchChange,
    onCreate,
    onView,
    onEdit,
    onImpersonate,
    onChangeStatus,
    onDelete,
    onRestore,
    onForceDelete,
    onAssignRole,
}: Props) {
    const [isSearching, setIsSearching] = useState(false);
    const [selectedUserIds, setSelectedUserIds] = useState<string[]>([]);
    const [bulkOperation, setBulkOperation] = useState<
        'archive' | 'force-delete' | null
    >(null);
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
    const requestFilters = (
        nextSearch = search,
        page = 1,
        perPage = pagination.perPage,
    ) => {
        setSelectedUserIds([]);
        setIsSearching(true);
        router.get(
            route('system.users.index'),
            {
                search: nextSearch.trim() || undefined,
                status: status === 'all' ? undefined : status,
                role: role === 'all' ? undefined : role,
                archive: archive === 'all' ? undefined : archive,
                page: page > 1 ? page : undefined,
                per_page: perPage === 25 ? undefined : perPage,
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
        requestFilters(search, 1);
    };
    const handleSearchChange = (value: string) => {
        onSearchChange(value);
        clearLiveSearchTimer();

        const query = value.trim();

        if (query.length > 0 && query.length < 3) {
            return;
        }

        debounceTimer.current = window.setTimeout(() => {
            requestFilters(value, 1);
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

    const changePage = (page: number) => {
        if (
            page < 1 ||
            page > pagination.lastPage ||
            page === pagination.currentPage
        ) {
            return;
        }

        requestFilters(search, page);
    };

    const changePerPage = (value: string) => {
        requestFilters(
            search,
            1,
            Number(value) as UserManagementPagination['perPage'],
        );
    };

    const selectableUsers = users.filter((user) => !user.isProtected);
    const selectedVisibleUserIds = selectedUserIds.filter((userId) =>
        selectableUsers.some((user) => user.id === userId),
    );
    const allVisibleSelected =
        selectableUsers.length > 0 &&
        selectedVisibleUserIds.length === selectableUsers.length;
    const canBulkArchive = canDelete && filters.archive !== 'archived';
    const canBulkForceDelete = canForceDelete && filters.archive === 'archived';
    const toggleUser = (userId: string, checked: boolean) => {
        setSelectedUserIds((current) =>
            checked
                ? [...new Set([...current, userId])]
                : current.filter((id) => id !== userId),
        );
    };
    const toggleAllVisibleUsers = (checked: boolean) => {
        setSelectedUserIds(
            checked ? selectableUsers.map((user) => user.id) : [],
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
                        <Select
                            name="status"
                            value={status}
                            onValueChange={setStatus}
                        >
                            <SelectTrigger
                                id="user-filter-status"
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
                        <Select
                            name="role"
                            value={role}
                            onValueChange={setRole}
                        >
                            <SelectTrigger
                                id="user-filter-role"
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
                            name="archive"
                            value={archive}
                            onValueChange={(value) =>
                                setArchive(
                                    value as UserManagementFilters['archive'],
                                )
                            }
                        >
                            <SelectTrigger
                                id="user-filter-archive"
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
                {selectedVisibleUserIds.length > 0 ? (
                    <div className="flex flex-col gap-3 border-t pt-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm font-medium">
                            {selectedVisibleUserIds.length} user dipilih
                        </p>
                        <div className="flex flex-wrap gap-2">
                            {canBulkArchive ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setBulkOperation('archive')}
                                >
                                    Arsipkan terpilih
                                </Button>
                            ) : null}
                            {canBulkForceDelete ? (
                                <Button
                                    type="button"
                                    variant="destructive"
                                    onClick={() =>
                                        setBulkOperation('force-delete')
                                    }
                                >
                                    Hapus permanen terpilih
                                </Button>
                            ) : null}
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={() => setSelectedUserIds([])}
                            >
                                Batal pilih
                            </Button>
                        </div>
                    </div>
                ) : null}
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
                                <th className="w-12 px-5 py-3">
                                    <Checkbox
                                        aria-label="Pilih semua user pada halaman"
                                        checked={allVisibleSelected}
                                        disabled={selectableUsers.length === 0}
                                        onCheckedChange={(checked) =>
                                            toggleAllVisibleUsers(
                                                checked === true,
                                            )
                                        }
                                    />
                                </th>
                                <th className="px-5 py-3 font-medium">User</th>
                                <th className="hidden px-5 py-3 font-medium xl:table-cell">
                                    Role
                                </th>
                                <th className="px-5 py-3 font-medium">
                                    Status
                                </th>
                                <th className="hidden px-5 py-3 font-medium xl:table-cell">
                                    Verifikasi
                                </th>
                                <th className="hidden px-5 py-3 font-medium xl:table-cell">
                                    Terakhir login
                                </th>
                                <th className="px-5 py-3 text-right font-medium">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border/70">
                            {users.map((user, index) => (
                                <UserTableRow
                                    key={user.id}
                                    user={user}
                                    index={index}
                                    isSelected={selectedVisibleUserIds.includes(
                                        user.id,
                                    )}
                                    canEdit={canEdit}
                                    canImpersonate={canImpersonate}
                                    canChangeStatus={canChangeStatus}
                                    canDelete={canDelete}
                                    canRestore={canRestore}
                                    canForceDelete={canForceDelete}
                                    canAssignRole={canAssignRole}
                                    onView={onView}
                                    onEdit={onEdit}
                                    onImpersonate={onImpersonate}
                                    onChangeStatus={onChangeStatus}
                                    onDelete={onDelete}
                                    onRestore={onRestore}
                                    onForceDelete={onForceDelete}
                                    onAssignRole={onAssignRole}
                                    onSelectedChange={toggleUser}
                                />
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            <div className="flex flex-col gap-3 border-t p-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p className="text-muted-foreground">
                    Menampilkan halaman {pagination.currentPage} dari{' '}
                    {pagination.lastPage} · {pagination.total} user
                </p>
                <div className="flex flex-wrap items-center gap-2">
                    <Select
                        name="per_page"
                        value={String(pagination.perPage)}
                        onValueChange={changePerPage}
                    >
                        <SelectTrigger
                            aria-label="Jumlah baris per halaman"
                            className="w-28"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {[5, 10, 25, 50].map((value) => (
                                <SelectItem key={value} value={String(value)}>
                                    {value} baris
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={isSearching || pagination.currentPage === 1}
                        onClick={() => changePage(pagination.currentPage - 1)}
                    >
                        Sebelumnya
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={
                            isSearching ||
                            pagination.currentPage === pagination.lastPage
                        }
                        onClick={() => changePage(pagination.currentPage + 1)}
                    >
                        Berikutnya
                    </Button>
                </div>
            </div>
            <BulkUserLifecycleDialog
                open={bulkOperation !== null}
                operation={bulkOperation}
                userIds={selectedVisibleUserIds}
                onOpenChange={(open) => !open && setBulkOperation(null)}
                onCompleted={() => setSelectedUserIds([])}
            />
        </section>
    );
}

type UserTableRowProps = Pick<
    Props,
    | 'canEdit'
    | 'canImpersonate'
    | 'canChangeStatus'
    | 'canDelete'
    | 'canRestore'
    | 'canForceDelete'
    | 'canAssignRole'
    | 'onView'
    | 'onEdit'
    | 'onImpersonate'
    | 'onChangeStatus'
    | 'onDelete'
    | 'onRestore'
    | 'onForceDelete'
    | 'onAssignRole'
> & {
    user: UserManagementUser;
    index: number;
    isSelected: boolean;
    onSelectedChange: (userId: string, checked: boolean) => void;
};

function UserTableRow({
    user,
    index,
    isSelected,
    canEdit,
    canImpersonate,
    canChangeStatus,
    canDelete,
    canRestore,
    canForceDelete,
    canAssignRole,
    onView,
    onEdit,
    onImpersonate,
    onChangeStatus,
    onDelete,
    onRestore,
    onForceDelete,
    onAssignRole,
    onSelectedChange,
}: UserTableRowProps) {
    const isArchived = user.deletedAt !== null;

    return (
        <tr className="dashboard-table-row transition-colors">
            <td className="hidden px-5 py-4 xl:table-cell">
                <Checkbox
                    aria-label={`Pilih ${user.name}`}
                    checked={isSelected}
                    disabled={user.isProtected}
                    onCheckedChange={(checked) =>
                        onSelectedChange(user.id, checked === true)
                    }
                />
            </td>
            <td className="hidden px-5 py-4 xl:table-cell">
                <button
                    id={`user-table-row-${index}`}
                    type="button"
                    onClick={() => onView(user)}
                    className="flex items-center gap-3 rounded-md text-left outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                    <Avatar className="size-9 rounded-lg">
                        {user.avatarUrl ? (
                            <AvatarImage src={user.avatarUrl} alt="" />
                        ) : null}
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
                {user.roles.length > 0 ? (
                    <div className="flex flex-wrap gap-1">
                        {user.roles.map((role) => (
                            <Badge
                                key={role}
                                variant="outline"
                                className="dashboard-badge"
                            >
                                {role}
                            </Badge>
                        ))}
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground">
                        Belum ada role
                    </span>
                )}
            </td>
            <td className="px-5 py-4">
                {isArchived ? (
                    <div className="space-y-1.5">
                        <Badge
                            variant="outline"
                            className="gap-1.5 border-slate-500/30 bg-slate-500/10 font-medium text-slate-700 dark:text-slate-300"
                        >
                            Diarsipkan
                        </Badge>
                        <p className="text-xs text-muted-foreground">
                            Status terakhir: {statusLabels[user.status]}
                        </p>
                    </div>
                ) : (
                    <Badge
                        variant="outline"
                        className={cn(
                            'gap-1.5 font-medium',
                            statusAccentClasses[user.status],
                        )}
                    >
                        {statusLabels[user.status]}
                    </Badge>
                )}
            </td>
            <td className="px-5 py-4">
                <Badge
                    variant="outline"
                    className={cn(
                        'font-medium',
                        user.emailVerified
                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                            : 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300',
                    )}
                >
                    {user.emailVerified ? 'Terverifikasi' : 'Belum verifikasi'}
                </Badge>
            </td>
            <td className="hidden px-5 py-4 text-xs text-muted-foreground xl:table-cell">
                {user.lastLoginAt
                    ? new Intl.DateTimeFormat('id-ID', {
                          dateStyle: 'medium',
                          timeStyle: 'short',
                      }).format(new Date(user.lastLoginAt))
                    : 'Belum pernah login'}
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
                    {isArchived && canRestore && !user.isProtected ? (
                        <UserActionButton
                            aria-label={`Pulihkan ${user.name}`}
                            onClick={() => onRestore(user)}
                            tooltip={`Pulihkan ${user.name}`}
                            className="text-emerald-600 hover:bg-emerald-500/10 hover:text-emerald-700 dark:text-emerald-300 dark:hover:text-emerald-200"
                        >
                            <RotateCcw className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {isArchived && canForceDelete && !user.isProtected ? (
                        <UserActionButton
                            aria-label={`Hapus permanen ${user.name}`}
                            onClick={() => onForceDelete(user)}
                            tooltip={`Hapus permanen ${user.name}`}
                            className="text-rose-600 hover:bg-rose-500/10 hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200"
                        >
                            <Trash2 className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {canEdit && !user.isProtected && !isArchived ? (
                        <UserActionButton
                            aria-label={`Edit ${user.name}`}
                            onClick={() => onEdit(user)}
                            tooltip={`Edit ${user.name}`}
                            className="text-violet-600 hover:bg-violet-500/10 hover:text-violet-700 dark:text-violet-300 dark:hover:text-violet-200"
                        >
                            <Edit3 className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {canImpersonate && !user.isProtected && !isArchived ? (
                        <UserActionButton
                            aria-label={`Impersonate ${user.name}`}
                            onClick={() => onImpersonate(user)}
                            tooltip={`Masuk sebagai ${user.name}`}
                            className="text-amber-600 hover:bg-amber-500/10 hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200"
                        >
                            <LogIn className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {canChangeStatus && !user.isProtected && !isArchived ? (
                        <UserActionButton
                            aria-label={`Ubah status ${user.name}`}
                            onClick={() => onChangeStatus(user)}
                            tooltip={`Ubah status ${user.name}`}
                            className="text-cyan-600 hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-cyan-300 dark:hover:text-cyan-200"
                        >
                            <Activity className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {canDelete && !user.isProtected && !isArchived ? (
                        <UserActionButton
                            aria-label={`Arsipkan ${user.name}`}
                            onClick={() => onDelete(user)}
                            tooltip={`Arsipkan ${user.name}`}
                            className="text-rose-600 hover:bg-rose-500/10 hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200"
                        >
                            <Trash2 className="size-4" />
                        </UserActionButton>
                    ) : null}
                    {canAssignRole && !user.isProtected && !isArchived ? (
                        <UserActionButton
                            aria-label={`Atur role ${user.name}`}
                            onClick={() => onAssignRole(user)}
                            tooltip={`Atur role ${user.name}`}
                            className="text-fuchsia-600 hover:bg-fuchsia-500/10 hover:text-fuchsia-700 dark:text-fuchsia-300 dark:hover:text-fuchsia-200"
                        >
                            <ShieldPlus className="size-4" />
                        </UserActionButton>
                    ) : null}
                </div>
            </td>
        </tr>
    );
}
