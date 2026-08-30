import type { StudentAdmission } from '../types';

type SummaryItem = {
    title: string;
    value: number;
    description: string;
};

type Props = {
    total: number;
    admissions: StudentAdmission[];
};

export function AdmissionSummary({ total, admissions }: Props) {
    const submitted = admissions.filter(
        (admission) => admission.status === 'submitted',
    ).length;
    const accepted = admissions.filter(
        (admission) => admission.status === 'accepted',
    ).length;

    const items: SummaryItem[] = [
        {
            title: 'Total pendaftaran',
            value: total,
            description: 'Seluruh calon santri dalam scope filter.',
        },
        {
            title: 'Diajukan di halaman ini',
            value: submitted,
            description: 'Menunggu proses verifikasi petugas.',
        },
        {
            title: 'Diterima di halaman ini',
            value: accepted,
            description: 'Siap menjadi sumber konversi data santri.',
        },
    ];

    return (
        <section className="grid gap-3 sm:grid-cols-3">
            {items.map((item) => (
                <SummaryCard key={item.title} {...item} />
            ))}
        </section>
    );
}

function SummaryCard({ title, value, description }: SummaryItem) {
    return (
        <article className="dashboard-card rounded-2xl border p-4">
            <p className="text-sm text-foreground/65">{title}</p>
            <p className="mt-2 text-2xl font-semibold">{value}</p>
            <p className="mt-1 text-xs text-foreground/60">{description}</p>
        </article>
    );
}
