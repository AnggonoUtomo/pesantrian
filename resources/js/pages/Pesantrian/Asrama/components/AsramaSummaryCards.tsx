import { BedDouble, DoorOpen, UsersRound } from 'lucide-react';
import type { Dormitory } from '../types';

type Props = {
    total: number;
    dormitories: Dormitory[];
};

export function AsramaSummaryCards({ total, dormitories }: Props) {
    const activeRooms = dormitories.reduce(
        (sum, dormitory) => sum + dormitory.room_count,
        0,
    );
    const occupied = dormitories.reduce(
        (sum, dormitory) => sum + dormitory.occupied_count,
        0,
    );

    return (
        <section
            aria-label="Ringkasan Asrama"
            className="grid gap-3 md:grid-cols-3"
        >
            <SummaryCard
                icon={BedDouble}
                label="Total asrama"
                value={String(total)}
                description="Jumlah asrama sesuai filter aktif."
            />
            <SummaryCard
                icon={DoorOpen}
                label="Kamar aktif"
                value={String(activeRooms)}
                description="Jumlah kamar dari data yang tampil."
            />
            <SummaryCard
                icon={UsersRound}
                label="Santri menempati"
                value={String(occupied)}
                description="Santri yang sedang aktif menempati kamar."
            />
        </section>
    );
}

type SummaryCardProps = {
    icon: typeof BedDouble;
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
