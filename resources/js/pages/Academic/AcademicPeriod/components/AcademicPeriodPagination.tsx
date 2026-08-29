import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { AcademicPeriodPaginationMeta } from '../types';

type AcademicPeriodPaginationProps = {
    meta: AcademicPeriodPaginationMeta;
    onPageChange: (page: number) => void;
};

export function AcademicPeriodPagination({
    meta,
    onPageChange,
}: AcademicPeriodPaginationProps) {
    if (meta.lastPage <= 1) {
        return null;
    }

    return (
        <nav
            className="flex items-center justify-between gap-3 border-t pt-4"
            aria-label="Pagination periode akademik"
        >
            <p className="text-xs text-foreground/60">
                Halaman {meta.currentPage} dari {meta.lastPage}
            </p>
            <div className="flex items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={meta.currentPage <= 1}
                    onClick={() => onPageChange(meta.currentPage - 1)}
                >
                    <ChevronLeft className="size-4" aria-hidden="true" />
                    Prev
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={meta.currentPage >= meta.lastPage}
                    onClick={() => onPageChange(meta.currentPage + 1)}
                >
                    Next
                    <ChevronRight className="size-4" aria-hidden="true" />
                </Button>
            </div>
        </nav>
    );
}
