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
import type { StudentPermitIndexPageProps } from '../types';

type Props = {
    search: string;
    dateFrom: string;
    dateTo: string;
    permitType: string;
    status: string;
    isLate: string;
    perPage: number;
    options: StudentPermitIndexPageProps['options'];
    onSearchChange: (value: string) => void;
    onDateFromChange: (value: string) => void;
    onDateToChange: (value: string) => void;
    onPermitTypeChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onIsLateChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function PerizinanSantriFilters({
    search,
    dateFrom,
    dateTo,
    permitType,
    status,
    isLate,
    perPage,
    options,
    onSearchChange,
    onDateFromChange,
    onDateToChange,
    onPermitTypeChange,
    onStatusChange,
    onIsLateChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar Perizinan Santri</h2>
                    <p className="text-sm text-foreground/65">
                        Cari izin berdasarkan nomor, NIS, nama santri, tujuan,
                        alasan, status, atau rentang tanggal.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_155px_155px] xl:grid-cols-[1fr_155px_155px_180px_170px_150px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari izin santri
                    </span>
                    <Input
                        id="student-permit-search"
                        type="search"
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder="Cari nomor, NIS, nama, atau tujuan"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Tanggal mulai
                    </span>
                    <Input
                        id="student-permit-date-from"
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
                        id="student-permit-date-to"
                        type="date"
                        value={dateTo}
                        onChange={(event) =>
                            onDateToChange(event.target.value)
                        }
                    />
                </label>
                <SelectField
                    label="Jenis izin"
                    value={permitType}
                    onValueChange={onPermitTypeChange}
                >
                    <SelectItem value="all">Semua jenis</SelectItem>
                    {options.permitTypes.map((type) => (
                        <SelectItem key={type.value} value={type.value}>
                            {type.label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Status izin"
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
                    label="Keterlambatan"
                    value={isLate}
                    onValueChange={onIsLateChange}
                >
                    <SelectItem value="all">Semua izin</SelectItem>
                    <SelectItem value="true">Terlambat</SelectItem>
                    <SelectItem value="false">Tidak terlambat</SelectItem>
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
