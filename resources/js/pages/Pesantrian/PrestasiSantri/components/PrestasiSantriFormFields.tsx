import type { ReactNode } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export function PrestasiTextField({
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
            {error ? <PrestasiFieldError message={error} /> : null}
        </div>
    );
}

export function PrestasiTextareaField({
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
            {error ? <PrestasiFieldError message={error} /> : null}
        </div>
    );
}

export function PrestasiSelectField({
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
            {error ? <PrestasiFieldError message={error} /> : null}
        </div>
    );
}

export function PrestasiFieldError({ message }: { message: string }) {
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
