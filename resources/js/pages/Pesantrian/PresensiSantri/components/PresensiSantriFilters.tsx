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
import type { StudentAttendanceIndexPageProps } from '../types';

type Props = {
    search: string;
    dateFrom: string;
    dateTo: string;
    contextType: string;
    status: string;
    perPage: number;
    options: StudentAttendanceIndexPageProps['options'];
    onSearchChange: (value: string) => void;
    onDateFromChange: (value: string) => void;
    onDateToChange: (value: string) => void;
    onContextTypeChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function PresensiSantriFilters({
    search,
    dateFrom,
    dateTo,
    contextType,
    status,
    perPage,
    options,
    onSearchChange,
    onDateFromChange,
    onDateToChange,
    onContextTypeChange,
    onStatusChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar Presensi Santri</h2>
                    <p className="text-sm text-foreground/65">
                        Cari sesi presensi berdasarkan kode, nama sesi, konteks,
                        tanggal, atau status.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_155px_155px] xl:grid-cols-[1fr_155px_155px_190px_170px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari sesi presensi
                    </span>
                    <Input
                        id="student-attendance-search"
                        type="search"
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder="Cari kode, sesi, atau konteks"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Tanggal mulai
                    </span>
                    <Input
                        id="student-attendance-date-from"
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
                        id="student-attendance-date-to"
                        type="date"
                        value={dateTo}
                        onChange={(event) =>
                            onDateToChange(event.target.value)
                        }
                    />
                </label>
                <SelectField
                    label="Konteks presensi"
                    value={contextType}
                    onValueChange={onContextTypeChange}
                >
                    <SelectItem value="all">Semua konteks</SelectItem>
                    {options.contexts.map((context) => (
                        <SelectItem key={context.value} value={context.value}>
                            {context.label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Status sesi"
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
