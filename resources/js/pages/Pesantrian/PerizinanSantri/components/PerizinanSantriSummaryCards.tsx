import { AlertTriangle, CheckCircle2, ClipboardList } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { StudentPermit } from '../types';

type Props = {
    total: number;
    permits: StudentPermit[];
};

export function PerizinanSantriSummaryCards({ total, permits }: Props) {
    const activeCount = permits.filter((permit) => permit.summary.is_active).length;
    const lateCount = permits.filter((permit) => permit.summary.is_late).length;

    return (
        <div className="grid gap-3 md:grid-cols-3">
            <SummaryCard
                icon={ClipboardList}
                label="Total izin"
                value={String(total)}
                helper="Sesuai filter aktif."
            />
            <SummaryCard
                icon={CheckCircle2}
                label="Izin aktif"
                value={String(activeCount)}
                helper="Submitted, approved, atau sedang izin di halaman ini."
            />
            <SummaryCard
                icon={AlertTriangle}
                label="Terlambat kembali"
                value={String(lateCount)}
                helper="Returned terlambat atau masih keluar melewati batas."
            />
        </div>
    );
}

function SummaryCard({
    icon: Icon,
    label,
    value,
    helper,
}: {
    icon: LucideIcon;
    label: string;
    value: string;
    helper: string;
}) {
    return (
        <section className="dashboard-card rounded-2xl border p-4">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm text-foreground/60">{label}</p>
                    <p className="mt-1 text-2xl font-semibold">{value}</p>
                    <p className="mt-1 text-xs text-foreground/55">{helper}</p>
                </div>
                <Icon className="size-5 text-cyan-600" aria-hidden="true" />
            </div>
        </section>
    );
}
