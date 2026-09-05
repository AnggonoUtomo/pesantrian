import type { ReactNode } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export function AsramaTextField({
    id,
    label,
    value,
    error,
    placeholder,
    type = 'text',
    required = false,
    maxLength,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
    type?: string;
    required?: boolean;
    maxLength?: number;
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
                maxLength={maxLength}
                aria-invalid={error ? true : undefined}
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <AsramaFieldError message={error} /> : null}
        </div>
    );
}

export function AsramaTextareaField({
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
                className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                value={value}
                placeholder={placeholder}
                required={required}
                aria-invalid={error ? true : undefined}
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <AsramaFieldError message={error} /> : null}
        </div>
    );
}

export function AsramaSelectField({
    id,
    label,
    value,
    error,
    children,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    children: ReactNode;
    onChange: (value: string) => void;
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
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
            {error ? <AsramaFieldError message={error} /> : null}
        </div>
    );
}

export function AsramaOption({
    value,
    children,
}: {
    value: string;
    children: ReactNode;
}) {
    return <SelectItem value={value}>{children}</SelectItem>;
}

export function AsramaFieldError({ message }: { message: string }) {
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

export function today(): string {
    return new Date().toISOString().slice(0, 10);
}
