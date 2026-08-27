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
import { typeOptions } from './organizationUnitDisplay';

type Props = {
    search: string;
    status: string;
    type: string;
    perPage: number;
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onTypeChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function OrganizationUnitFilterForm({
    search,
    status,
    type,
    perPage,
    onSearchChange,
    onStatusChange,
    onTypeChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar unit</h2>
                    <p className="text-sm text-foreground/65">
                        Gunakan pencarian dan filter untuk menemukan unit
                        organisasi yang dibutuhkan.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_180px_220px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari unit
                    </span>
                    <Input
                        id="organization-unit-search"
                        type="search"
                        value={search}
                        onChange={(event) =>
                            onSearchChange(event.target.value)
                        }
                        placeholder="Cari nama atau kode unit"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Status
                    </span>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger>
                            <SelectValue placeholder="Semua status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Jenis unit
                    </span>
                    <Select value={type} onValueChange={onTypeChange}>
                        <SelectTrigger>
                            <SelectValue placeholder="Semua jenis" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua jenis</SelectItem>
                            {typeOptions.map(([value, label]) => (
                                <SelectItem key={value} value={value}>
                                    {label}
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
