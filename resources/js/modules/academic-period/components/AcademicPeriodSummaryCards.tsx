import { CalendarClock, CalendarRange, CheckCircle2, Layers3 } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

type AcademicPeriodSummaryCardsProps = {
    totalYears: number;
    activeYears: number;
    totalTerms: number;
    activeTerms: number;
    canManage: boolean;
};

export function AcademicPeriodSummaryCards({
    totalYears,
    activeYears,
    totalTerms,
    activeTerms,
    canManage,
}: AcademicPeriodSummaryCardsProps) {
    return (
        <section
            className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Ringkasan periode akademik"
        >
            <SummaryCard
                icon={CalendarRange}
                label="Tahun akademik"
                value={totalYears}
                helper={`${activeYears} aktif pada hasil saat ini`}
            />
            <SummaryCard
                icon={Layers3}
                label="Term akademik"
                value={totalTerms}
                helper="Semester atau pembagian periode belajar"
            />
            <SummaryCard
                icon={CheckCircle2}
                label="Term aktif"
                value={activeTerms}
                helper="Backend menjaga satu term aktif global"
            />
            <SummaryCard
                icon={CalendarClock}
                label="Mode halaman"
                value={canManage ? 'Kelola' : 'Lihat'}
                helper="Mutation disiapkan di increment UI berikutnya"
            />
        </section>
    );
}

function SummaryCard({
    icon: Icon,
    label,
    value,
    helper,
}: {
    icon: typeof CalendarRange;
    label: string;
    value: number | string;
    helper: string;
}) {
    return (
        <Card className="py-4">
            <CardContent className="flex items-start gap-3 px-4">
                <span className="rounded-lg bg-primary/10 p-2 text-primary">
                    <Icon className="size-4" aria-hidden="true" />
                </span>
                <span className="min-w-0">
                    <span className="block text-sm text-foreground/65">
                        {label}
                    </span>
                    <span className="mt-1 block text-2xl font-semibold">
                        {value}
                    </span>
                    <span className="mt-1 block text-xs text-foreground/60">
                        {helper}
                    </span>
                </span>
            </CardContent>
        </Card>
    );
}
