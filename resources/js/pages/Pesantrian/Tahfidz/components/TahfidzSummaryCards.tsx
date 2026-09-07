import { BookOpenCheck, ClipboardCheck, RotateCcw } from 'lucide-react';
import type { TahfidzSubmission } from '../types';

type Props = {
    total: number;
    submissions: TahfidzSubmission[];
};

export function TahfidzSummaryCards({ total, submissions }: Props) {
    const accepted = submissions.filter(
        (submission) => submission.status === 'accepted',
    ).length;
    const needsFollowUp = submissions.filter(
        (submission) =>
            submission.status === 'submitted' ||
            submission.status === 'needs_revision',
    ).length;

    return (
        <div className="grid gap-3 md:grid-cols-3">
            <SummaryCard
                icon={BookOpenCheck}
                label="Total setoran"
                value={String(total)}
                helper="Sesuai filter aktif."
            />
            <SummaryCard
                icon={ClipboardCheck}
                label="Setoran diterima"
                value={String(accepted)}
                helper="Jumlah diterima pada halaman ini."
            />
            <SummaryCard
                icon={RotateCcw}
                label="Butuh tindak lanjut"
                value={String(needsFollowUp)}
                helper="Menunggu review atau perlu koreksi."
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
    icon: typeof BookOpenCheck;
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
                <Icon className="size-5 text-emerald-600" aria-hidden="true" />
            </div>
        </section>
    );
}
