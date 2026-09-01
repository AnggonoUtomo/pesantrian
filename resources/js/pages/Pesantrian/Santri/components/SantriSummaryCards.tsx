import { GraduationCap, UserRoundCheck, UsersRound } from 'lucide-react';
import type { Student } from '../types';

type Props = {
    total: number;
    students: Student[];
};

export function SantriSummaryCards({ total, students }: Props) {
    const active = students.filter((student) => student.status === 'active');
    const withGuardian = students.filter(
        (student) => student.primary_guardian !== null,
    );

    return (
        <section
            aria-label="Ringkasan data induk santri"
            className="grid gap-3 md:grid-cols-3"
        >
            <SummaryCard
                icon={UsersRound}
                label="Total santri"
                value={String(total)}
                description="Jumlah sesuai filter aktif."
            />
            <SummaryCard
                icon={UserRoundCheck}
                label="Aktif di halaman ini"
                value={String(active.length)}
                description="Santri dengan status aktif pada halaman ini."
            />
            <SummaryCard
                icon={GraduationCap}
                label="Wali snapshot"
                value={String(withGuardian.length)}
                description="Data yang memiliki wali utama."
            />
        </section>
    );
}

type SummaryCardProps = {
    icon: typeof UsersRound;
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
                <Icon
                    className="size-5 text-primary"
                    aria-hidden="true"
                />
            </div>
            <p className="mt-3 text-xs text-foreground/60">{description}</p>
        </article>
    );
}
