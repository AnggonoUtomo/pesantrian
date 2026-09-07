import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export function TahfidzTextField({
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
            {error ? <TahfidzFieldError message={error} /> : null}
        </div>
    );
}

export function TahfidzTextareaField({
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
                className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring min-h-24 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                onChange={(event) => onChange(event.target.value)}
            />
            {error ? <TahfidzFieldError message={error} /> : null}
        </div>
    );
}

export function TahfidzSelectField({
    id,
    label,
    value,
    error,
    placeholder = 'Pilih data',
    children,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    error?: string;
    placeholder?: string;
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
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
            {error ? <TahfidzFieldError message={error} /> : null}
        </div>
    );
}

export function TahfidzFieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}

export function TahfidzCancelButton({
    disabled,
    onClick,
}: {
    disabled: boolean;
    onClick: () => void;
}) {
    return (
        <Button
            type="button"
            variant="outline"
            disabled={disabled}
            onClick={onClick}
        >
            Batal
        </Button>
    );
}

export function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}
