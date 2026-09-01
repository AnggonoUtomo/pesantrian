import { Filter } from 'lucide-react';
import type { FormEvent } from 'react';
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
import type { PrimaryUnitOption } from '../types';
import { studentStatusOptions } from './santriDisplay';

type Props = {
    search: string;
    status: string;
    primaryUnitId: string;
    perPage: number;
    primaryUnitOptions: PrimaryUnitOption[];
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onPrimaryUnitChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function SantriFilters({
    search,
    status,
    primaryUnitId,
    perPage,
    primaryUnitOptions,
    onSearchChange,
    onStatusChange,
    onPrimaryUnitChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar data induk santri</h2>
                    <p className="text-sm text-foreground/65">
                        Cari santri berdasarkan NIS, nama lengkap, atau nama
                        panggilan.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_180px_240px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari santri
                    </span>
                    <Input
                        id="student-search"
                        type="search"
                        value={search}
                        onChange={(event) =>
                            onSearchChange(event.target.value)
                        }
                        placeholder="Cari NIS atau nama"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Status santri
                    </span>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger>
                            <SelectValue placeholder="Semua status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua status</SelectItem>
                            {studentStatusOptions.map(([value, label]) => (
                                <SelectItem key={value} value={value}>
                                    {label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Unit utama
                    </span>
                    <Select
                        value={primaryUnitId}
                        onValueChange={onPrimaryUnitChange}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Semua unit" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua unit</SelectItem>
                            {primaryUnitOptions.map((unit) => (
                                <SelectItem key={unit.id} value={unit.id}>
                                    {unit.name} ({unit.code})
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>
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
