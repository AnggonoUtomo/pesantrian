import { Award, CircleCheck, History, ListChecks } from 'lucide-react';
import type { StudentAchievement } from '../types';

type Props = {
    total: number;
    achievements: StudentAchievement[];
};

export function PrestasiSantriSummaryCards({ total, achievements }: Props) {
    const needsAction = achievements.filter(
        (achievement) => achievement.summary.needs_action,
    ).length;
    const finalCount = achievements.filter(
        (achievement) => achievement.summary.is_final,
    ).length;
    const verifiedCount = achievements.filter(
        (achievement) => achievement.status === 'verified',
    ).length;

    return (
        <div className="grid gap-3 md:grid-cols-4">
            <SummaryCard
                label="Total prestasi"
                value={total}
                icon={Award}
                tone="dashboard-card--yellow"
            />
            <SummaryCard
                label="Butuh tindak lanjut"
                value={needsAction}
                icon={ListChecks}
                tone="dashboard-card--amber"
            />
            <SummaryCard
                label="Riwayat final"
                value={finalCount}
                icon={History}
                tone="dashboard-card--blue"
            />
            <SummaryCard
                label="Terverifikasi"
                value={verifiedCount}
                icon={CircleCheck}
                tone="dashboard-card--green"
            />
        </div>
    );
}

function SummaryCard({
    label,
    value,
    icon: Icon,
    tone,
}: {
    label: string;
    value: number;
    icon: typeof Award;
    tone: string;
}) {
    return (
        <section className={`dashboard-card ${tone} rounded-2xl border p-4`}>
            <div className="flex items-center justify-between gap-3">
                <div>
                    <p className="text-sm text-foreground/60">{label}</p>
                    <p className="mt-1 text-2xl font-semibold">{value}</p>
                </div>
                <Icon className="size-9 text-foreground/35" aria-hidden="true" />
            </div>
        </section>
    );
}
