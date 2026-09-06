import { AlertTriangle, CheckCircle2, ClipboardList } from 'lucide-react';
import type { StudentAttendance } from '../types';
import { followUpCount } from './presensiSantriDisplay';

type Props = {
    total: number;
    attendances: StudentAttendance[];
};

export function PresensiSantriSummaryCards({ total, attendances }: Props) {
    const submitted = attendances.filter(
        (attendance) => attendance.status === 'submitted',
    ).length;
    const followUps = attendances.reduce(
        (sum, attendance) => sum + followUpCount(attendance.summary),
        0,
    );

    return (
        <div className="grid gap-3 md:grid-cols-3">
            <SummaryCard
                icon={ClipboardList}
                label="Total sesi"
                value={String(total)}
                helper="Sesuai filter aktif."
            />
            <SummaryCard
                icon={CheckCircle2}
                label="Sesi final"
                value={String(submitted)}
                helper="Jumlah sesi submit di halaman ini."
            />
            <SummaryCard
                icon={AlertTriangle}
                label="Butuh tindak lanjut"
                value={String(followUps)}
                helper="Terlambat, izin, sakit, atau alfa."
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
    icon: typeof ClipboardList;
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
                <Icon className="size-5 text-sky-600" aria-hidden="true" />
            </div>
        </section>
    );
}
