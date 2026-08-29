import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AcademicPeriodStatus, AcademicYear } from '../types';

type TextFieldProps = {
    id: string;
    label: string;
    type?: string;
    value: string;
    error?: string;
    placeholder?: string;
    maxLength?: number;
    min?: number;
    max?: number;
    onChange: (value: string) => void;
};

export function AcademicPeriodTextField({
    id,
    label,
    type = 'text',
    value,
    error,
    placeholder,
    maxLength,
    min,
    max,
    onChange,
}: TextFieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                type={type}
                value={value}
                placeholder={placeholder}
                maxLength={maxLength}
                min={min}
                max={max}
                required
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? (
                <p className="text-xs text-destructive" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}

export function AcademicPeriodStatusField({
    value,
    error,
    onChange,
}: {
    value: AcademicPeriodStatus;
    error?: string;
    onChange: (value: AcademicPeriodStatus) => void;
}) {
    return (
        <div className="space-y-2">
            <Label>Status</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger aria-label="Status" className="w-full">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="closed">Ditutup</SelectItem>
                </SelectContent>
            </Select>
            {error ? (
                <p className="text-xs text-destructive" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}

export function AcademicYearSelectField({
    years,
    value,
    error,
    onChange,
}: {
    years: AcademicYear[];
    value: string;
    error?: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label>Tahun akademik</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger aria-label="Tahun akademik" className="w-full">
                    <SelectValue placeholder="Pilih tahun akademik" />
                </SelectTrigger>
                <SelectContent>
                    {years.map((year) => (
                        <SelectItem key={year.id} value={year.id}>
                            {year.name} ({year.code})
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error ? (
                <p className="text-xs text-destructive" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}
