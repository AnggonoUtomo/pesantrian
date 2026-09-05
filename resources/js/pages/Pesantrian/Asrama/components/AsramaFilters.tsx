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
import type { DormitoryArchiveFilter, DormitoryIndexPageProps } from '../types';
import { dormitoryStatusOptions, genderPolicyOptions } from './asramaDisplay';

type Props = {
    search: string;
    status: string;
    archived: DormitoryArchiveFilter;
    genderPolicy: string;
    unitId: string;
    perPage: number;
    options: DormitoryIndexPageProps['options'];
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchivedChange: (value: DormitoryArchiveFilter) => void;
    onGenderPolicyChange: (value: string) => void;
    onUnitChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function AsramaFilters({
    search,
    status,
    archived,
    genderPolicy,
    unitId,
    perPage,
    options,
    onSearchChange,
    onStatusChange,
    onArchivedChange,
    onGenderPolicyChange,
    onUnitChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar Asrama</h2>
                    <p className="text-sm text-foreground/65">
                        Cari asrama berdasarkan kode, nama, unit, status, atau
                        kebijakan putra/putri.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_170px_170px] xl:grid-cols-[1fr_170px_170px_190px_190px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari asrama
                    </span>
                    <Input
                        id="dormitory-search"
                        type="search"
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder="Cari kode atau nama asrama"
                    />
                </label>
                <SelectField
                    label="Status asrama"
                    value={status}
                    onValueChange={onStatusChange}
                >
                    <SelectItem value="all">Semua status</SelectItem>
                    {dormitoryStatusOptions.map(([value, label]) => (
                        <SelectItem key={value} value={value}>
                            {label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Status arsip"
                    value={archived}
                    onValueChange={(value) =>
                        onArchivedChange(value as DormitoryArchiveFilter)
                    }
                >
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="archived">Diarsipkan</SelectItem>
                </SelectField>
                <SelectField
                    label="Tipe penghuni"
                    value={genderPolicy}
                    onValueChange={onGenderPolicyChange}
                >
                    <SelectItem value="all">Semua tipe</SelectItem>
                    {genderPolicyOptions.map(([value, label]) => (
                        <SelectItem key={value} value={value}>
                            {label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Unit asrama"
                    value={unitId}
                    onValueChange={onUnitChange}
                >
                    <SelectItem value="all">Semua unit</SelectItem>
                    {options.units.map((unit) => (
                        <SelectItem key={unit.id} value={unit.id}>
                            {unit.name}
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
