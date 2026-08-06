import { Head, usePage } from '@inertiajs/react';
import { Keyboard, ScrollText, ShieldCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { AuditLogDetailDialog } from '../components/AuditLogDetailDialog';
import { AuditLogFilterBar } from '../components/AuditLogFilterBar';
import { AuditLogSummaryCards } from '../components/AuditLogSummaryCards';
import { AuditLogTable } from '../components/AuditLogTable';
import type { AuditLogPageProps, AuditLogRecord } from '../types';

export default function Index() {
    const { auth, auditLogs, filters, errors } =
        usePage<AuditLogPageProps>().props;
    const [selectedRecord, setSelectedRecord] = useState<AuditLogRecord | null>(
        null,
    );
    const [loading, setLoading] = useState(false);
    const canView =
        auth.superSystem === true ||
        auth.permissions?.['audit_log.view'] === true;

    useEffect(() => {
        const handleShortcut = (event: KeyboardEvent) => {
            const target = event.target;
            const isTyping =
                target instanceof HTMLElement &&
                (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
                    target.isContentEditable);

            if (event.key === 'Escape' && selectedRecord) {
                setSelectedRecord(null);

                return;
            }

            if (!isTyping && event.key === '/') {
                event.preventDefault();
                document.getElementById('audit-log-search')?.focus();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [selectedRecord]);

    if (!canView) {
        return (
            <>
                <Head title="Audit Log" />
                <SystemDashboardLayout
                    title="Audit Log"
                    description="Tinjau histori aktivitas penting pada area System."
                >
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-8 text-center">
                        <ShieldCheck className="mx-auto size-10 text-rose-500" />
                        <h2 className="mt-3 text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-foreground/65">
                            Akun ini tidak memiliki permission membaca audit
                            log.
                        </p>
                    </section>
                </SystemDashboardLayout>
            </>
        );
    }

    return (
        <>
            <Head title="Audit Log" />
            <SystemDashboardLayout
                eyebrow="System security"
                title="Audit Log"
                description="Telusuri actor, aktivitas, subject, dan correlation ID tanpa membuka data sensitif."
                actions={
                    <span className="dashboard-badge dashboard-badge--blue inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium">
                        <ScrollText className="size-4" />
                        Append-only
                    </span>
                }
            >
                <div className="space-y-5">
                    <AuditLogSummaryCards
                        records={auditLogs.data}
                        total={auditLogs.meta.total}
                    />
                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 className="font-semibold">
                                    Workspace audit
                                </h2>
                                <p className="text-sm text-foreground/65">
                                    Filter dan buka detail record yang tersedia
                                    untuk scope akun ini.
                                </p>
                            </div>
                            <div className="flex items-center gap-2 text-xs text-foreground/65">
                                <Keyboard className="size-4" />
                                <kbd className="rounded border px-1.5 py-0.5">
                                    /
                                </kbd>
                                Fokus pencarian
                                <kbd className="rounded border px-1.5 py-0.5">
                                    Esc
                                </kbd>
                                Tutup detail
                            </div>
                        </div>
                        <AuditLogFilterBar
                            filters={filters}
                            loading={loading}
                            onLoadingChange={setLoading}
                        />
                        {errors && Object.keys(errors).length > 0 ? (
                            <p
                                role="alert"
                                className="dashboard-message--error rounded-lg px-3 py-2 text-sm"
                            >
                                Filter audit tidak valid. Periksa input dan coba
                                kembali.
                            </p>
                        ) : null}
                        <AuditLogTable
                            auditLogs={auditLogs}
                            loading={loading}
                            onLoadingChange={setLoading}
                            onView={setSelectedRecord}
                        />
                    </section>
                </div>
            </SystemDashboardLayout>
            <AuditLogDetailDialog
                record={selectedRecord}
                onOpenChange={(open) => !open && setSelectedRecord(null)}
            />
        </>
    );
}
