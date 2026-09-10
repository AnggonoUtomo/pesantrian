import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginationMeta } from '../types';

type Props = {
    meta: PaginationMeta;
    pagination: {
        perPageOptions: number[];
        defaultPerPage: number;
    };
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
};

export function PrestasiSantriPagination({
    meta,
    pagination,
    onPageChange,
    onPerPageChange,
}: Props) {
    const canGoPrevious = meta.currentPage > 1;
    const canGoNext = meta.currentPage < meta.lastPage;

    return (
        <div className="flex flex-col gap-3 border-t pt-4 text-sm text-foreground/65 sm:flex-row sm:items-center sm:justify-between">
            <p>
                Halaman {meta.currentPage} dari {meta.lastPage || 1} · Total{' '}
                {meta.total} prestasi
            </p>
            <div className="flex flex-wrap items-center gap-2">
                <Select
                    value={String(meta.perPage)}
                    onValueChange={(value) => onPerPageChange(Number(value))}
                >
                    <SelectTrigger
                        className="w-28"
                        aria-label="Jumlah per halaman"
                    >
                        <SelectValue placeholder="Per halaman" />
                    </SelectTrigger>
                    <SelectContent>
                        {pagination.perPageOptions.map((option) => (
                            <SelectItem key={option} value={String(option)}>
                                {option}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Button
                    type="button"
                    variant="outline"
                    disabled={!canGoPrevious}
                    onClick={() => onPageChange(meta.currentPage - 1)}
                >
                    Sebelumnya
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    disabled={!canGoNext}
                    onClick={() => onPageChange(meta.currentPage + 1)}
                >
                    Berikutnya
                </Button>
            </div>
        </div>
    );
}
