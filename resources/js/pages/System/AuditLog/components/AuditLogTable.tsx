import { router } from '@inertiajs/react';
import { Eye, ScrollText } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import route from '@/lib/route';
import type { AuditLogPage, AuditLogRecord } from '../types';

type Props = {
    auditLogs: AuditLogPage;
    loading: boolean;
    onLoadingChange: (loading: boolean) => void;
    onView: (record: AuditLogRecord) => void;
};

export function AuditLogTable({
    auditLogs,
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
                {auditLogs.data.map((record) => (
                    <article
                        key={record.id}
                        className="dashboard-subcard space-y-3 rounded-xl border p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <Badge className="dashboard-badge dashboard-badge--emerald max-w-full truncate">
                                    {record.action}
                                </Badge>
                                <p className="mt-1 text-xs text-foreground/60">
                                    {record.module} ·{' '}
                                    {formatDate(record.createdAt)}
                                </p>
                            </div>
                            <Button
                                type="button"
                                size="icon"
                                variant="outline"
                                onClick={() => onView(record)}
                                aria-label={`Lihat detail ${record.action}`}
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
                                    {record.subjectType}
                                </dd>
                            </div>
                            <div className="col-span-2">
                                <dt className="text-xs text-foreground/60">
                                    Correlation ID
                                </dt>
                                <dd className="truncate font-mono text-xs">
                                    {record.correlationId}
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
                            <th className="px-4 py-3 font-medium">Waktu</th>
                            <th className="px-4 py-3 font-medium">Actor</th>
                            <th className="px-4 py-3 font-medium">Aktivitas</th>
                            <th className="px-4 py-3 font-medium">Subject</th>
                            <th className="px-4 py-3 font-medium">
                                Correlation
                            </th>
                            <th className="px-4 py-3 text-right font-medium">
                                Detail
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {auditLogs.data.map((record) => (
                            <tr
                                key={record.id}
                                className="dashboard-table-row border-t"
                            >
                                <td className="px-4 py-3 whitespace-nowrap">
                                    {formatDate(record.createdAt)}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium">
                                        {record.actorName ?? 'System'}
                                    </p>
                                    <p className="max-w-36 truncate text-xs text-foreground/60">
                                        {record.actorId ?? 'system-process'}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    <Badge className="dashboard-badge dashboard-badge--emerald">
                                        {record.action}
                                    </Badge>
                                    <p className="mt-1 text-xs text-foreground/60">
                                        {record.module}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    <p>{record.subjectType}</p>
                                    <p className="max-w-36 truncate text-xs text-foreground/60">
                                        {record.subjectId ?? '-'}
                                    </p>
                                </td>
                                <td className="max-w-48 truncate px-4 py-3 font-mono text-xs">
                                    {record.correlationId}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => onView(record)}
                                        aria-label={`Lihat detail ${record.action}`}
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
                <div className="flex gap-2">
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
