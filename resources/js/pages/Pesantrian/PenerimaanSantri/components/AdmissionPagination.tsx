import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AdmissionPageProps } from '../types';

type Props = {
    meta: AdmissionPageProps['admissions']['meta'];
    pagination: AdmissionPageProps['pagination'];
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
};

export function AdmissionPagination({
    meta,
    pagination,
    onPageChange,
    onPerPageChange,
}: Props) {
    const firstItem =
        meta.total === 0 ? 0 : (meta.currentPage - 1) * meta.perPage + 1;
    const lastItem = Math.min(meta.currentPage * meta.perPage, meta.total);

    return (
        <div className="flex flex-col gap-3 border-t pt-4 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p className="text-foreground/65">
                Menampilkan {firstItem}-{lastItem} dari {meta.total}{' '}
                pendaftaran · halaman {meta.currentPage} dari {meta.lastPage}
            </p>
            <div className="flex flex-wrap items-center gap-2">
                <Select
                    name="per_page"
                    value={String(meta.perPage)}
                    onValueChange={(value) => onPerPageChange(Number(value))}
                >
                    <SelectTrigger
                        aria-label="Jumlah pendaftaran per halaman"
                        className="w-28"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {pagination.perPageOptions.map((option) => (
                            <SelectItem key={option} value={String(option)}>
                                {option} baris
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Button
                    type="button"
                    variant="outline"
                    disabled={meta.currentPage <= 1}
                    onClick={() => onPageChange(meta.currentPage - 1)}
                >
                    Sebelumnya
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    disabled={meta.currentPage >= meta.lastPage}
                    onClick={() => onPageChange(meta.currentPage + 1)}
                >
                    Berikutnya
                </Button>
            </div>
        </div>
    );
}
