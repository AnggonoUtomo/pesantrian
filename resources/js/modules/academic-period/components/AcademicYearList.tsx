import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { AcademicPeriodPaginationMeta, AcademicYear } from '../types';
import { AcademicPeriodPagination } from './AcademicPeriodPagination';
import { AcademicPeriodStatusBadge } from './AcademicPeriodStatusBadge';

type AcademicYearListProps = {
    years: AcademicYear[];
    meta: AcademicPeriodPaginationMeta;
    onPageChange: (page: number) => void;
};

export function AcademicYearList({
    years,
    meta,
    onPageChange,
}: AcademicYearListProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Tahun akademik</CardTitle>
                <CardDescription>
                    Menyimpan rentang besar seperti 2026/2027.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {years.length > 0 ? (
                    <ul className="space-y-3" aria-label="Daftar tahun akademik">
                        {years.map((year) => (
                            <li
                                key={year.id}
                                className="rounded-xl border p-4"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 className="text-sm font-semibold">
                                            {year.name}
                                        </h2>
                                        <p className="mt-1 text-xs text-foreground/60">
                                            {year.code} · {year.starts_on} s/d{' '}
                                            {year.ends_on}
                                        </p>
                                    </div>
                                    <AcademicPeriodStatusBadge
                                        status={year.status}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada tahun akademik yang cocok dengan filter.
                    </p>
                )}
                <AcademicPeriodPagination
                    meta={meta}
                    onPageChange={onPageChange}
                />
            </CardContent>
        </Card>
    );
}
