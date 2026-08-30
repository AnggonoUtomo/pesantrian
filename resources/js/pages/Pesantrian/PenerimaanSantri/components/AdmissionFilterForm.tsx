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
import type { AdmissionTargetUnitOption } from '../types';
import {
    admissionStatusOptions,
    registrationFeeStatusOptions,
} from './admissionDisplay';

type Props = {
    search: string;
    status: string;
    targetUnitId: string;
    registrationFeeStatus: string;
    perPage: number;
    targetUnitOptions: AdmissionTargetUnitOption[];
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onTargetUnitChange: (value: string) => void;
    onRegistrationFeeStatusChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
};

export function AdmissionFilterForm({
    search,
    status,
    targetUnitId,
    registrationFeeStatus,
    perPage,
    targetUnitOptions,
    onSearchChange,
    onStatusChange,
    onTargetUnitChange,
    onRegistrationFeeStatusChange,
    onSubmit,
    onReset,
}: Props) {
    return (
        <>
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 className="font-semibold">Daftar penerimaan santri</h2>
                    <p className="text-sm text-foreground/65">
                        Cari calon santri berdasarkan nomor pendaftaran, nama
                        calon, atau nama wali.
                    </p>
                </div>
                <Badge variant="outline" className="gap-2">
                    <Filter className="size-3.5" />
                    {perPage} per halaman
                </Badge>
            </div>

            <form
                className="grid gap-3 xl:grid-cols-[1fr_170px_220px_180px_auto]"
                onSubmit={onSubmit}
            >
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Cari calon santri
                    </span>
                    <Input
                        id="admission-search"
                        type="search"
                        value={search}
                        onChange={(event) =>
                            onSearchChange(event.target.value)
                        }
                        placeholder="Cari nama, wali, atau nomor"
                    />
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Status pendaftaran
                    </span>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger>
                            <SelectValue placeholder="Semua status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua status</SelectItem>
                            {admissionStatusOptions.map(([value, label]) => (
                                <SelectItem key={value} value={value}>
                                    {label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Unit tujuan
                    </span>
                    <Select
                        value={targetUnitId}
                        onValueChange={onTargetUnitChange}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Semua unit" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua unit</SelectItem>
                            {targetUnitOptions.map((unit) => (
                                <SelectItem key={unit.id} value={unit.id}>
                                    {unit.name} ({unit.code})
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>
                <label className="space-y-1.5">
                    <span className="text-xs font-medium text-foreground/70">
                        Status biaya
                    </span>
                    <Select
                        value={registrationFeeStatus}
                        onValueChange={onRegistrationFeeStatusChange}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Semua biaya" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua biaya</SelectItem>
                            {registrationFeeStatusOptions.map(
                                ([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ),
                            )}
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
