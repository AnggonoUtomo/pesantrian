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

export function EmployeeSummary({ total, active, inactive }: Props) {
    const items: SummaryItem[] = [
        {
            title: 'Total employee',
            value: total,
            description: 'Seluruh SDM dalam scope filter.',
        },
        {
            title: 'Aktif di halaman ini',
            value: active,
            description: 'Employee yang masih aktif.',
        },
        {
            title: 'Nonaktif di halaman ini',
            value: inactive,
            description: 'Employee yang sudah dinonaktifkan.',
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
