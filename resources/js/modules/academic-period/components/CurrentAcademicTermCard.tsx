import { CalendarCheck2 } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { AcademicTerm } from '../types';
import { AcademicPeriodStatusBadge } from './AcademicPeriodStatusBadge';

export function CurrentAcademicTermCard({
    currentTerm,
}: {
    currentTerm: AcademicTerm | null;
}) {
    return (
        <Card className="dashboard-card dashboard-card--green">
            <CardHeader>
                <div className="flex items-center gap-2">
                    <CalendarCheck2
                        className="size-5 text-primary"
                        aria-hidden="true"
                    />
                    <CardTitle>Term aktif saat ini</CardTitle>
                </div>
                <CardDescription>
                    Source of truth global untuk module akademik, finance, dan
                    reporting ketika consumer sudah disetujui.
                </CardDescription>
            </CardHeader>
            <CardContent>
                {currentTerm ? (
                    <div className="flex flex-col gap-3 rounded-xl border bg-background/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex flex-wrap items-center gap-2">
                                <h2 className="text-base font-semibold">
                                    {currentTerm.name}
                                </h2>
                                <AcademicPeriodStatusBadge
                                    status={currentTerm.status}
                                />
                            </div>
                            <p className="mt-1 text-sm text-foreground/65">
                                {currentTerm.code} · {currentTerm.starts_on} s/d{' '}
                                {currentTerm.ends_on}
                            </p>
                        </div>
                        <p className="text-xs font-medium tracking-wide text-primary uppercase">
                            Aktif
                        </p>
                    </div>
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada term aktif. Gunakan lifecycle activation pada
                        increment UI berikutnya setelah form mutation disiapkan.
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
