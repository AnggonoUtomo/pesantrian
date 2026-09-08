import { CheckCircle2, ClipboardList, ListTodo } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { StudentDisciplineCase } from '../types';

type Props = {
    total: number;
    cases: StudentDisciplineCase[];
};

export function KedisiplinanSantriSummaryCards({
    total,
    cases,
}: Props) {
    const needsAction = cases.filter((item) => item.summary.needs_action).length;
    const finalCount = cases.filter((item) => item.summary.is_final).length;

    return (
        <div className="grid gap-3 md:grid-cols-3">
            <SummaryCard
                icon={ClipboardList}
                label="Total kasus"
                value={String(total)}
                helper="Sesuai filter aktif."
            />
            <SummaryCard
                icon={ListTodo}
                label="Butuh tindak lanjut"
                value={String(needsAction)}
                helper="Dihitung dari data pada halaman ini."
            />
            <SummaryCard
                icon={CheckCircle2}
                label="Kasus final"
                value={String(finalCount)}
                helper="Kasus selesai atau dibatalkan pada halaman ini."
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
                <Icon className="size-5 text-amber-600" aria-hidden="true" />
            </div>
        </section>
    );
}
