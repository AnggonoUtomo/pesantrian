type SummaryItem = {
    title: string;
    value: number;
    description: string;
};

type Props = {
    total: number;
    active: number;
    inactive: number;
};

export function OrganizationUnitSummary({ total, active, inactive }: Props) {
    const items: SummaryItem[] = [
        {
            title: 'Total unit',
            value: total,
            description: 'Seluruh unit dalam scope filter.',
        },
        {
            title: 'Aktif di halaman ini',
            value: active,
            description: 'Unit yang masih berjalan.',
        },
        {
            title: 'Nonaktif di halaman ini',
            value: inactive,
            description: 'Unit yang diarsipkan dari operasional.',
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
