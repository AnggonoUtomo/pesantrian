import { BookOpenCheck, School, UsersRound } from 'lucide-react';
import type { ClassGroup } from '../types';

type Props = {
    total: number;
    classGroups: ClassGroup[];
};

export function KelasRombelSummaryCards({ total, classGroups }: Props) {
    const active = classGroups.filter(
        (classGroup) => classGroup.status === 'active',
    );
    const capacity = classGroups.reduce(
        (sum, classGroup) => sum + (classGroup.capacity ?? 0),
        0,
    );

    return (
        <section
            aria-label="Ringkasan Kelas / Rombel / Kurikulum"
            className="grid gap-3 md:grid-cols-3"
        >
            <SummaryCard
                icon={School}
                label="Total rombel"
                value={String(total)}
                description="Jumlah rombel sesuai filter aktif."
            />
            <SummaryCard
                icon={BookOpenCheck}
                label="Aktif di halaman ini"
                value={String(active.length)}
                description="Rombel operasional pada halaman saat ini."
            />
            <SummaryCard
                icon={UsersRound}
                label="Kapasitas halaman"
                value={String(capacity)}
                description="Akumulasi kapasitas dari data yang tampil."
            />
        </section>
    );
}

type SummaryCardProps = {
    icon: typeof School;
    label: string;
    value: string;
    description: string;
};

function SummaryCard({
    icon: Icon,
    label,
    value,
    description,
}: SummaryCardProps) {
    return (
        <article className="dashboard-card rounded-2xl border p-4">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm text-foreground/65">{label}</p>
                    <p className="mt-1 text-2xl font-semibold">{value}</p>
                </div>
                <Icon className="size-5 text-primary" aria-hidden="true" />
            </div>
            <p className="mt-3 text-xs text-foreground/60">{description}</p>
        </article>
    );
}
