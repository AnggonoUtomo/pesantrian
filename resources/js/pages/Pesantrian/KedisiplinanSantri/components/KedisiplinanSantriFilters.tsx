import { Filter } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { StudentDisciplineIndexPageProps } from '../types';

type Props = {
    search: string;
    dateFrom: string;
    dateTo: string;
    status: string;
    severity: string;
    categoryId: string;
    perPage: number;
    options: StudentDisciplineIndexPageProps['options'];
    onSearchChange: (value: string) => void;
    onDateFromChange: (value: string) => void;
    onDateToChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onSeverityChange: (value: string) => void;
    onCategoryChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function KedisiplinanSantriFilters({
    search,
    dateFrom,
    dateTo,
    status,
    severity,
    categoryId,
    perPage,
    options,
    onSearchChange,
    onDateFromChange,
    onDateToChange,
    onStatusChange,
    onSeverityChange,
    onCategoryChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">
                        Daftar Pelanggaran / Kedisiplinan
                    </h2>
                    <p className="text-sm text-foreground/65">
                        Cari kasus kedisiplinan berdasarkan nomor kasus, NIS,
                        nama santri, unit, lokasi, kategori, status, tingkat,
                        atau rentang tanggal.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_155px_155px] xl:grid-cols-[1fr_155px_155px_180px_150px_180px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari kasus kedisiplinan
                    </span>
                    <Input
                        id="student-discipline-search"
                        type="search"
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder="Cari nomor kasus, NIS, nama, atau lokasi"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Tanggal mulai
                    </span>
                    <Input
                        id="student-discipline-date-from"
                        type="date"
                        value={dateFrom}
                        onChange={(event) =>
                            onDateFromChange(event.target.value)
                        }
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Tanggal akhir
                    </span>
                    <Input
                        id="student-discipline-date-to"
                        type="date"
                        value={dateTo}
                        onChange={(event) =>
                            onDateToChange(event.target.value)
                        }
                    />
                </label>
                <SelectField
                    label="Status kasus"
                    value={status}
                    onValueChange={onStatusChange}
                >
                    <SelectItem value="all">Semua status</SelectItem>
                    {options.statuses.map((statusOption) => (
                        <SelectItem
                            key={statusOption.value}
                            value={statusOption.value}
                        >
                            {statusOption.label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Tingkat"
                    value={severity}
                    onValueChange={onSeverityChange}
                >
                    <SelectItem value="all">Semua tingkat</SelectItem>
                    {options.severities.map((severityOption) => (
                        <SelectItem
                            key={severityOption.value}
                            value={severityOption.value}
                        >
                            {severityOption.label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Kategori"
                    value={categoryId}
                    onValueChange={onCategoryChange}
                >
                    <SelectItem value="all">Semua kategori</SelectItem>
                    {options.categories.map((category) => (
                        <SelectItem key={category.value} value={category.value}>
                            {category.label}
                        </SelectItem>
                    ))}
                </SelectField>
                <div className="flex items-end gap-2">
                    <Button type="submit">Terapkan</Button>
                    <Button type="button" variant="outline" onClick={onReset}>
                        Reset
                    </Button>
                </div>
            </form>
        </>
    );
}

function SelectField({
    label,
    value,
    onValueChange,
    children,
}: {
    label: string;
    value: string;
    onValueChange: (value: string) => void;
    children: ReactNode;
}) {
    return (
        <label className="space-y-1.5">
            <span className="text-xs font-medium text-foreground/70">
                {label}
            </span>
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger>
                    <SelectValue placeholder={label} />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
        </label>
    );
}
