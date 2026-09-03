import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { ClassGroupStatus, ReferenceOption } from '../types';

type TextFieldProps = {
    id: string;
    label: string;
    value: string | number | null;
    error?: string;
    type?: 'text' | 'number' | 'date';
    placeholder?: string;
    maxLength?: number;
    onChange: (value: string) => void;
};

type SelectFieldProps = {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    options: ReferenceOption[];
    nullableLabel?: string;
    onChange: (value: string) => void;
};

export function KelasRombelTextField({
    id,
    label,
    value,
    error,
    type = 'text',
    placeholder,
    maxLength,
    onChange,
}: TextFieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                type={type}
                value={value ?? ''}
                placeholder={placeholder}
                maxLength={maxLength}
                aria-invalid={Boolean(error)}
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <p className="text-xs text-destructive">{error}</p> : null}
        </div>
    );
}

export function KelasRombelTextareaField({
    id,
    label,
    value,
    error,
    placeholder,
    onChange,
}: Omit<TextFieldProps, 'type' | 'maxLength'>) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <textarea
                id={id}
                value={value ?? ''}
                placeholder={placeholder}
                aria-invalid={Boolean(error)}
                className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <p className="text-xs text-destructive">{error}</p> : null}
        </div>
    );
}

export function KelasRombelSelectField({
    id,
    label,
    value,
    error,
    placeholder = 'Pilih data',
    options,
    nullableLabel,
    onChange,
}: SelectFieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger id={id} className="w-full" aria-invalid={Boolean(error)}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {nullableLabel ? (
                        <SelectItem value="__none">{nullableLabel}</SelectItem>
                    ) : null}
                    {options.map((option) => (
                        <SelectItem key={option.id} value={option.id}>
                            {option.name} ({option.code})
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error ? <p className="text-xs text-destructive">{error}</p> : null}
        </div>
    );
}

export function KelasRombelStatusField({
    value,
    error,
    onChange,
}: {
    value: ClassGroupStatus;
    error?: string;
    onChange: (value: ClassGroupStatus) => void;
}) {
    return (
        <div className="space-y-2">
            <Label>Status</Label>
            <Select value={value} onValueChange={(next) => onChange(next as ClassGroupStatus)}>
                <SelectTrigger className="w-full" aria-invalid={Boolean(error)}>
                    <SelectValue placeholder="Pilih status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="closed">Ditutup</SelectItem>
                    <SelectItem value="archived">Arsip</SelectItem>
                </SelectContent>
            </Select>
            {error ? <p className="text-xs text-destructive">{error}</p> : null}
        </div>
    );
}
