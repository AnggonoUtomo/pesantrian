import type { ReactNode } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export function PerizinanTextField({
    id,
    label,
    value,
    error,
    placeholder,
    type = 'text',
    required = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    type?: string;
    required?: boolean;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                type={type}
                value={value}
                placeholder={placeholder}
                required={required}
                aria-invalid={error ? true : undefined}
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <PerizinanFieldError message={error} /> : null}
        </div>
    );
}

export function PerizinanTextareaField({
    id,
    label,
    value,
    error,
    placeholder,
    required = false,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    required?: boolean;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <textarea
                id={id}
                value={value}
                placeholder={placeholder}
                required={required}
                aria-invalid={error ? true : undefined}
                className="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <PerizinanFieldError message={error} /> : null}
        </div>
    );
}

export function PerizinanSelectField({
    id,
    label,
    value,
    error,
    placeholder,
    onChange,
    children,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    onChange: (value: string) => void;
    children: ReactNode;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger
                    id={id}
                    className="w-full"
                    aria-invalid={error ? true : undefined}
                >
                    <SelectValue placeholder={placeholder ?? label} />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
            {error ? <PerizinanFieldError message={error} /> : null}
        </div>
    );
}

export function PerizinanFieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}

export function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}

export function toDateTimeLocal(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value.replace(' ', 'T').slice(0, 16);
    }

    const timezoneOffset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - timezoneOffset).toISOString().slice(0, 16);
}

export function fromDateTimeLocal(value: string): string {
    const trimmed = value.trim();

    if (trimmed === '') {
        return '';
    }

    return `${trimmed.replace('T', ' ').slice(0, 16)}:00`;
}
