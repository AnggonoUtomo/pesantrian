import {
    CalendarClock,
    MessageSquareText,
    ScrollText,
    ShieldCheck,
} from 'lucide-react';
import type { AuditLogRecord } from '../types';

type Props = {
    records: AuditLogRecord[];
    total: number;
};

export function AuditLogSummaryCards({ records, total }: Props) {
    const modules = new Set(records.map((record) => record.moduleLabel)).size;
    const today = new Date().toDateString();
    const todayCount = records.filter(
        (record) => new Date(record.createdAt).toDateString() === today,
    ).length;
    const withReason = records.filter(
        (record) => record.reason !== null,
    ).length;
    const cards = [
        {
            label: 'Record terlihat',
            value: total,
            helper: 'Sesuai scope akun',
            icon: ScrollText,
            tone: 'dashboard-card--violet',
        },
        {
            label: 'Aktivitas hari ini',
            value: todayCount,
            helper: 'Pada halaman aktif',
            icon: CalendarClock,
            tone: 'dashboard-card--cyan',
        },
        {
            label: 'Module terlibat',
            value: modules,
            helper: 'Pada halaman aktif',
            icon: ShieldCheck,
            tone: 'dashboard-card--emerald',
        },
        {
            label: 'Dengan alasan',
            value: withReason,
            helper: 'Pada halaman aktif',
            icon: MessageSquareText,
            tone: 'dashboard-card--amber',
        },
    ];

    return (
        <section
            className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Ringkasan audit log"
        >
            {cards.map((card) => (
                <article
                    key={card.label}
                    className={`dashboard-card ${card.tone} rounded-2xl border p-4`}
                >
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <p className="text-sm text-foreground/70">
                                {card.label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold tabular-nums">
                                {card.value}
                            </p>
                            <p className="mt-1 text-xs text-foreground/60">
                                {card.helper}
                            </p>
                        </div>
                        <span className="dashboard-icon rounded-xl p-2.5">
                            <card.icon className="size-5" aria-hidden="true" />
                        </span>
                    </div>
                </article>
            ))}
        </section>
    );
}
