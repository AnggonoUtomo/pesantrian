import { CheckCircle2 } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { AcademicPeriodPaginationMeta, AcademicTerm } from '../types';
import { AcademicPeriodPagination } from './AcademicPeriodPagination';
import { AcademicPeriodStatusBadge } from './AcademicPeriodStatusBadge';

type AcademicTermListProps = {
    terms: AcademicTerm[];
    meta: AcademicPeriodPaginationMeta;
    onPageChange: (page: number) => void;
};

export function AcademicTermList({
    terms,
    meta,
    onPageChange,
}: AcademicTermListProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Term / semester</CardTitle>
                <CardDescription>
                    Pembagian periode belajar di dalam tahun akademik.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {terms.length > 0 ? (
                    <ul className="space-y-3" aria-label="Daftar term akademik">
                        {terms.map((term) => (
                            <li
                                key={term.id}
                                className="rounded-xl border p-4"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h2 className="text-sm font-semibold">
                                                {term.name}
                                            </h2>
                                            {term.is_active ? (
                                                <span className="inline-flex items-center gap-1 text-xs font-medium text-primary">
                                                    <CheckCircle2
                                                        className="size-3.5"
                                                        aria-hidden="true"
                                                    />
                                                    Aktif
                                                </span>
                                            ) : null}
                                        </div>
                                        <p className="mt-1 text-xs text-foreground/60">
                                            {term.code} · urutan {term.sequence}{' '}
                                            · {term.starts_on} s/d{' '}
                                            {term.ends_on}
                                        </p>
                                    </div>
                                    <AcademicPeriodStatusBadge
                                        status={term.status}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada term akademik yang cocok dengan filter.
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
