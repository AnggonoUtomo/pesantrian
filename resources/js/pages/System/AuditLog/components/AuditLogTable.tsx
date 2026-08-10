import { router } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Eye, ScrollText } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import route from '@/lib/route';
import type {
    AuditLogFilters,
    AuditLogPage,
    AuditLogPageProps,
    AuditLogRecord,
} from '../types';

type Props = {
    auditLogs: AuditLogPage;
    filters: AuditLogFilters;
    pagination: AuditLogPageProps['pagination'];
    loading: boolean;
    onLoadingChange: (loading: boolean) => void;
    onView: (record: AuditLogRecord) => void;
};

export function AuditLogTable({
    auditLogs,
    filters,
    pagination,
    loading,
    onLoadingChange,
    onView,
}: Props) {
    const formatDate = (value: string) =>
        new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));

    const visitPage = (page: number) => {
        onLoadingChange(true);
        router.get(
            route('system.audit-logs.index'),
            {
                ...Object.fromEntries(
                    new URLSearchParams(window.location.search),
                ),
                page,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => onLoadingChange(false),
            },
        );
    };

    const changePerPage = (value: string) => {
        onLoadingChange(true);
        router.get(
            route('system.audit-logs.index'),
            {
                ...Object.fromEntries(
                    new URLSearchParams(window.location.search),
                ),
                per_page: value,
                page: undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => onLoadingChange(false),
            },
        );
    };

    const toggleCreatedAtSort = () => {
        const sortDirection = filters.sort_direction === 'asc' ? 'desc' : 'asc';

        onLoadingChange(true);
        router.get(
            route('system.audit-logs.index'),
            {
                ...Object.fromEntries(
                    new URLSearchParams(window.location.search),
                ),
                sort_direction:
                    sortDirection === 'desc' ? undefined : sortDirection,
                page: undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => onLoadingChange(false),
            },
        );
    };

    if (auditLogs.data.length === 0) {
        return (
            <div className="dashboard-subcard rounded-xl border px-6 py-12 text-center">
                <ScrollText className="mx-auto size-10 text-primary" />
                <h2 className="mt-3 font-semibold">Belum ada audit log</h2>
                <p className="mt-1 text-sm text-foreground/65">
                    Ubah filter atau jalankan aktivitas yang memang perlu
                    diaudit.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div className="space-y-3 md:hidden" aria-label="Daftar audit log">
                {auditLogs.data.map((record, index) => (
                    <article
                        key={`${record.createdAt}-${record.actionLabel}-${index}`}
                        className="dashboard-subcard space-y-3 rounded-xl border p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <Badge className="dashboard-badge dashboard-badge--emerald max-w-full truncate">
                                    {record.actionLabel}
                                </Badge>
                                <p className="mt-1 text-xs text-foreground/60">
                                    {record.settingChange?.category ??
                                        record.moduleLabel}{' '}
                                    · {formatDate(record.createdAt)}
                                </p>
                            </div>
                            <Button
                                type="button"
                                size="icon"
                                variant="outline"
                                onClick={() => onView(record)}
                                aria-label={`Lihat detail ${record.actionLabel}`}
                            >
                                <Eye className="size-4" />
                            </Button>
                        </div>
                        <dl className="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt className="text-xs text-foreground/60">
                                    Actor
                                </dt>
                                <dd className="truncate font-medium">
                                    {record.actorName ?? 'System'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs text-foreground/60">
                                    Subject
                                </dt>
                                <dd className="truncate">
                                    {record.subjectLabel}
                                </dd>
                            </div>
                        </dl>
                    </article>
                ))}
            </div>
            <div className="hidden overflow-x-auto rounded-xl border md:block">
                <table className="w-full min-w-[860px] text-left text-sm">
                    <thead className="dashboard-table-header">
                        <tr>
                            <th className="px-4 py-3 font-medium">
                                <button
                                    type="button"
                                    onClick={toggleCreatedAtSort}
                                    className="inline-flex items-center gap-1"
                                    aria-label={`Urutkan waktu dari ${
                                        filters.sort_direction === 'asc'
                                            ? 'terbaru'
                                            : 'terlama'
                                    }`}
                                >
                                    Waktu
                                    {filters.sort_direction === 'asc' ? (
                                        <ArrowUp className="size-3" />
                                    ) : (
                                        <ArrowDown className="size-3" />
                                    )}
                                </button>
                            </th>
                            <th className="px-4 py-3 font-medium">Actor</th>
                            <th className="px-4 py-3 font-medium">Aktivitas</th>
                            <th className="px-4 py-3 font-medium">Subject</th>
                            <th className="px-4 py-3 text-right font-medium">
                                Detail
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {auditLogs.data.map((record, index) => (
                            <tr
                                key={`${record.createdAt}-${record.actionLabel}-${index}`}
                                className="dashboard-table-row border-t"
                            >
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {formatDate(record.createdAt)}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium">
                                        {record.actorName ?? 'System'}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    <Badge className="dashboard-badge dashboard-badge--emerald">
                                        {record.actionLabel}
                                    </Badge>
                                    <p className="mt-1 text-xs text-foreground/60">
                                        {record.settingChange?.category ??
                                            record.moduleLabel}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    <p>{record.subjectLabel}</p>
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => onView(record)}
                                        aria-label={`Lihat detail ${record.actionLabel}`}
                                    >
                                        <Eye className="size-4" />
                                        Lihat
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p className="text-foreground/65">
                    Halaman {auditLogs.meta.currentPage} dari{' '}
                    {auditLogs.meta.lastPage} - {auditLogs.meta.total} record
                </p>
                <div className="flex flex-wrap items-center gap-2">
                    <Select
                        name="per_page"
                        value={String(auditLogs.meta.perPage)}
                        onValueChange={changePerPage}
                    >
                        <SelectTrigger
                            aria-label="Jumlah baris per halaman"
                            className="w-28"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {pagination.perPageOptions.map((option) => (
                                <SelectItem key={option} value={String(option)}>
                                    {option} baris
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={loading || auditLogs.meta.currentPage <= 1}
                        onClick={() =>
                            visitPage(auditLogs.meta.currentPage - 1)
                        }
                    >
                        Sebelumnya
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={
                            loading ||
                            auditLogs.meta.currentPage >=
                                auditLogs.meta.lastPage
                        }
                        onClick={() =>
                            visitPage(auditLogs.meta.currentPage + 1)
                        }
                    >
                        Berikutnya
                    </Button>
                </div>
            </div>
        </div>
    );
}
