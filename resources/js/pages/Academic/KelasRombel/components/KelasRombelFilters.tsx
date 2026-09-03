import { Filter } from 'lucide-react';
import type { FormEvent } from 'react';
import type { ReactNode } from 'react';
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
import type {
    ClassGroupArchiveFilter,
    ClassGroupIndexPageProps,
} from '../types';
import { classGroupStatusOptions } from './kelasRombelDisplay';

type Props = {
    search: string;
    status: string;
    archived: ClassGroupArchiveFilter;
    academicYearId: string;
    academicTermId: string;
    unitId: string;
    curriculumId: string;
    perPage: number;
    options: ClassGroupIndexPageProps['options'];
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchivedChange: (value: ClassGroupArchiveFilter) => void;
    onAcademicYearChange: (value: string) => void;
    onAcademicTermChange: (value: string) => void;
    onUnitChange: (value: string) => void;
    onCurriculumChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function KelasRombelFilters({
    search,
    status,
    archived,
    academicYearId,
    academicTermId,
    unitId,
    curriculumId,
    perPage,
    options,
    onSearchChange,
    onStatusChange,
    onArchivedChange,
    onAcademicYearChange,
    onAcademicTermChange,
    onUnitChange,
    onCurriculumChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">
                        Daftar Kelas / Rombel / Kurikulum
                    </h2>
                    <p className="text-sm text-foreground/65">
                        Cari rombel berdasarkan kode, nama, unit, kelas, atau
                        kurikulum.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" aria-hidden="true" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 lg:grid-cols-[1fr_170px_170px] xl:grid-cols-[1fr_170px_170px_190px_190px_190px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari rombel
                    </span>
                    <Input
                        id="class-group-search"
                        type="search"
                        value={search}
                        onChange={(event) =>
                            onSearchChange(event.target.value)
                        }
                        placeholder="Cari kode atau nama rombel"
                    />
                </label>
                <SelectField
                    label="Status rombel"
                    value={status}
                    onValueChange={onStatusChange}
                >
                    <SelectItem value="all">Semua status</SelectItem>
                    {classGroupStatusOptions.map(([value, label]) => (
                        <SelectItem key={value} value={value}>
                            {label}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Status arsip"
                    value={archived}
                    onValueChange={(value) =>
                        onArchivedChange(value as ClassGroupArchiveFilter)
                    }
                >
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="archived">Diarsipkan</SelectItem>
                </SelectField>
                <SelectField
                    label="Tahun ajaran"
                    value={academicYearId}
                    onValueChange={onAcademicYearChange}
                >
                    <SelectItem value="all">Semua tahun</SelectItem>
                    {options.academicYears.map((year) => (
                        <SelectItem key={year.id} value={year.id}>
                            {year.name}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Semester"
                    value={academicTermId}
                    onValueChange={onAcademicTermChange}
                >
                    <SelectItem value="all">Semua semester</SelectItem>
                    {options.academicTerms.map((term) => (
                        <SelectItem key={term.id} value={term.id}>
                            {term.name}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Kurikulum"
                    value={curriculumId}
                    onValueChange={onCurriculumChange}
                >
                    <SelectItem value="all">Semua kurikulum</SelectItem>
                    {options.curricula.map((curriculum) => (
                        <SelectItem key={curriculum.id} value={curriculum.id}>
                            {curriculum.name}
                        </SelectItem>
                    ))}
                </SelectField>
                <SelectField
                    label="Unit"
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
