import { router } from '@inertiajs/react';
import { RotateCcw, Search } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import route from '@/lib/route';
import type { AuditLogFilters } from '../types';

type Props = {
    filters: AuditLogFilters;
    loading: boolean;
    onLoadingChange: (loading: boolean) => void;
};

export function AuditLogFilterBar({
    filters,
    loading,
    onLoadingChange,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [module, setModule] = useState(filters.module ?? '');
    const [action, setAction] = useState(filters.action ?? '');
    const [dateFrom, setDateFrom] = useState(filters.date_from ?? '');
    const [dateTo, setDateTo] = useState(filters.date_to ?? '');

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        onLoadingChange(true);
        router.get(
            route('system.audit-logs.index'),
            {
                search: search || undefined,
                module: module || undefined,
                action: action || undefined,
                date_from: dateFrom || undefined,
                date_to: dateTo || undefined,
                per_page: filters.per_page ?? 25,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => onLoadingChange(false),
            },
        );
    };

    const reset = () => {
        setSearch('');
        setModule('');
        setAction('');
        setDateFrom('');
        setDateTo('');
        onLoadingChange(true);
        router.get(
            route('system.audit-logs.index'),
            {},
            {
                preserveState: false,
                onFinish: () => onLoadingChange(false),
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="dashboard-subcard rounded-xl border p-4"
            aria-label="Filter audit log"
        >
            <div className="grid gap-3 lg:grid-cols-6">
                <label className="relative lg:col-span-2">
                    <span className="sr-only">Cari audit log</span>
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-foreground/60" />
                    <Input
                        id="audit-log-search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Cari action, subject, correlation..."
                        className="pl-9"
                    />
                </label>
                <Input
                    id="audit-log-module"
                    name="module"
                    value={module}
                    onChange={(event) => setModule(event.target.value)}
                    placeholder="Module"
                    aria-label="Filter module"
                />
                <Input
                    id="audit-log-action"
                    name="action"
                    value={action}
                    onChange={(event) => setAction(event.target.value)}
                    placeholder="Action"
                    aria-label="Filter action"
                />
                <Input
                    id="audit-log-date-from"
                    name="date_from"
                    type="date"
                    value={dateFrom}
                    onChange={(event) => setDateFrom(event.target.value)}
                    aria-label="Tanggal mulai"
                />
                <Input
                    id="audit-log-date-to"
                    name="date_to"
                    type="date"
                    value={dateTo}
                    onChange={(event) => setDateTo(event.target.value)}
                    aria-label="Tanggal selesai"
                />
            </div>
            <div className="mt-3 flex flex-wrap justify-end gap-2">
                <Button type="button" variant="outline" onClick={reset}>
                    <RotateCcw className="size-4" />
                    Reset
                </Button>
                <Button type="submit" disabled={loading}>
                    <Search className="size-4" />
                    {loading ? 'Mencari...' : 'Terapkan filter'}
                </Button>
            </div>
        </form>
    );
}
