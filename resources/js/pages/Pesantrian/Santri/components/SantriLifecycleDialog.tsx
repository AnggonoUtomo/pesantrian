import { useForm } from '@inertiajs/react';
import { RefreshCcw } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoadingButton } from '@/components/ui/loading-button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type { Student, StudentLifecyclePayload, StudentStatus } from '../types';
import { studentStatusOptions } from './santriDisplay';

type Props = {
    open: boolean;
    student: Student | null;
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof StudentLifecyclePayload | 'payload';

export function SantriLifecycleDialog({
    open,
    student,
    onOpenChange,
}: Props) {
    const form = useForm<StudentLifecyclePayload>({
        status: student?.status ?? 'active',
        reason: student?.status_reason ?? null,
    });
    const errors = form.errors as Partial<Record<ErrorKey, string>>;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (student === null) {
            return;
        }

        form.patch(
            routeOr(
                `/pesantrian/students/${student.id}/lifecycle`,
                'pesantrian.students.lifecycle',
                student.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <RefreshCcw className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Ubah status santri</DialogTitle>
                            <DialogDescription className="mt-1">
                                Perubahan status lifecycle dicatat bersama
                                alasan operator.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? <FieldError message={errors.payload} /> : null}

                <form className="space-y-4" onSubmit={submit}>
                    <div className="space-y-2">
                        <Label htmlFor="student-lifecycle-status">
                            Status baru
                        </Label>
                        <Select
                            value={form.data.status}
                            onValueChange={(value) =>
                                form.setData('status', value as StudentStatus)
                            }
                        >
                            <SelectTrigger
                                id="student-lifecycle-status"
                                aria-invalid={errors.status ? true : undefined}
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {studentStatusOptions.map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.status ? (
                            <FieldError message={errors.status} />
                        ) : null}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="student-lifecycle-reason">
                            Alasan status
                        </Label>
                        <Input
                            id="student-lifecycle-reason"
                            value={form.data.reason ?? ''}
                            placeholder="Contoh: pindah ke pesantren lain"
                            aria-invalid={errors.reason ? true : undefined}
                            onChange={(event) =>
                                form.setData('reason', nullable(event.target.value))
                            }
                        />
                        <p className="text-xs text-foreground/60">
                            Wajib diisi untuk status nonaktif, pindah, atau
                            lulus.
                        </p>
                        {errors.reason ? (
                            <FieldError message={errors.reason} />
                        ) : null}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            Simpan status
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function FieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}

function nullable(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}
