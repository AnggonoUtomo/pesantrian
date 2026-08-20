import { Head, usePage } from '@inertiajs/react';
import { ScrollText, ShieldCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { AuditLogDetailDialog } from '../components/AuditLogDetailDialog';
import { AuditLogFilterBar } from '../components/AuditLogFilterBar';
import { AuditLogShortcutBar } from '../components/AuditLogShortcutBar';
import { AuditLogSummaryCards } from '../components/AuditLogSummaryCards';
import { AuditLogTable } from '../components/AuditLogTable';
import type { AuditLogPageProps, AuditLogRecord } from '../types';

export default function Index() {
    const { auth, auditLogs, filters, pagination, errors } =
        usePage<AuditLogPageProps>().props;
    const [selectedRecord, setSelectedRecord] = useState<AuditLogRecord | null>(
        null,
    );
    const [loading, setLoading] = useState(false);
    const canView = canAccess(auth, 'audit_log.view');

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
                description="Telusuri actor, aktivitas, dan subject tanpa membuka data sensitif."
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
                    <AuditLogShortcutBar />
                    {errors && Object.keys(errors).length > 0 ? (
                        <p
                            role="alert"
                            className="dashboard-message--error text-sm"
                        >
                            Filter audit tidak valid. Periksa input dan coba
                            kembali.
                        </p>
                    ) : null}
                    <section className="dashboard-card dashboard-card--blue space-y-4 rounded-2xl border p-4 sm:p-5">
                        <div>
                            <h2 className="font-semibold">Riwayat aktivitas</h2>
                            <p className="text-sm text-foreground/65">
                                Filter dan buka detail record yang tersedia
                                untuk scope akun ini.
                            </p>
                        </div>
                        <AuditLogFilterBar
                            filters={filters}
                            loading={loading}
                            onLoadingChange={setLoading}
                        />
                        <AuditLogTable
                            auditLogs={auditLogs}
                            filters={filters}
                            pagination={pagination}
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
