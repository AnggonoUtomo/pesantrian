import { Search, X } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AcademicPeriodStatus } from '../types';

type AcademicPeriodFilterBarProps = {
    yearSearch: string;
    termSearch: string;
    yearStatus: AcademicPeriodStatus | 'all';
    termStatus: AcademicPeriodStatus | 'all';
    perPage: number;
    perPageOptions: number[];
    onYearSearchChange: (value: string) => void;
    onTermSearchChange: (value: string) => void;
    onYearStatusChange: (value: AcademicPeriodStatus | 'all') => void;
    onTermStatusChange: (value: AcademicPeriodStatus | 'all') => void;
    onPerPageChange: (value: number) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function AcademicPeriodFilterBar({
    yearSearch,
    termSearch,
    yearStatus,
    termStatus,
    perPage,
    perPageOptions,
    onYearSearchChange,
    onTermSearchChange,
    onYearStatusChange,
    onTermStatusChange,
    onPerPageChange,
    onSubmit,
    onReset,
}: AcademicPeriodFilterBarProps) {
    return (
        <form
            onSubmit={onSubmit}
            className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5"
        >
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                <div className="space-y-2">
                    <Label htmlFor="academic-year-search">Cari tahun</Label>
                    <Input
                        id="academic-year-search"
                        type="search"
                        value={yearSearch}
                        onChange={(event) =>
                            onYearSearchChange(event.target.value)
                        }
                        placeholder="Contoh: 2026 atau Ganjil"
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="academic-term-search">Cari term</Label>
                    <Input
                        id="academic-term-search"
                        type="search"
                        value={termSearch}
                        onChange={(event) =>
                            onTermSearchChange(event.target.value)
                        }
                        placeholder="Contoh: semester ganjil"
                    />
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button type="submit">
                        <Search className="size-4" aria-hidden="true" />
                        Terapkan
                    </Button>
                    <Button type="button" variant="outline" onClick={onReset}>
                        <X className="size-4" aria-hidden="true" />
                        Reset
                    </Button>
                </div>
            </div>
            <div className="mt-4 grid gap-4 sm:grid-cols-3">
                <FilterSelect
                    label="Status tahun"
                    value={yearStatus}
                    onValueChange={onYearStatusChange}
                />
                <FilterSelect
                    label="Status term"
                    value={termStatus}
                    onValueChange={onTermStatusChange}
                />
                <div className="space-y-2">
                    <Label>Baris per daftar</Label>
                    <Select
                        value={String(perPage)}
                        onValueChange={(value) =>
                            onPerPageChange(Number(value))
                        }
                    >
                        <SelectTrigger className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {perPageOptions.map((option) => (
                                <SelectItem
                                    key={option}
                                    value={String(option)}
                                >
                                    {option} data
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </form>
    );
}

function FilterSelect({
    label,
    value,
    onValueChange,
}: {
    label: string;
    value: AcademicPeriodStatus | 'all';
    onValueChange: (value: AcademicPeriodStatus | 'all') => void;
}) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger className="w-full">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua status</SelectItem>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="closed">Ditutup</SelectItem>
                </SelectContent>
            </Select>
        </div>
    );
}
